<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    private int $perFile = 100;
    private int $perAuthor = 50;
    private int $perPublisher = 50;

    public function booksIndex(): Response
    {
        $xml = Cache::remember('sitemap.books.index', 86400, function () {

            $total = DB::table('products')
                ->where('status', 1)
                ->count();

            if ($total === 0) {
                // Empty index is valid; just no <sitemap> children
                return $this->wrapSitemapIndex('');
            }

            $pages = (int) ceil($total / $this->perFile);

            $items = '';
            for ($i = 1; $i <= $pages; $i++) {
                $loc = URL::to("/sitemaps/books-{$i}/file.xml");
                $items .= '<sitemap>'
                        . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                        . '</sitemap>';
            }


            // ===== AUTHORS =====
            $totalAuthors = DB::table('users')->where('user_type', 2)->where('status', 1)->count();
            $authorPages = $totalAuthors > 0 ? (int)ceil($totalAuthors / $this->perAuthor) : 0;

            for ($i = 1; $i <= $authorPages; $i++) {
                $loc = URL::to("/sitemaps/authors-{$i}/file.xml");
                $items .= '<sitemap>'
                        . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                        . '</sitemap>';
            }


            // ===== PUBLISHERS ===== (NEW)
            $totalPublishers = DB::table('brands')->where('status', 1)->count();
            $publisherPages  = $totalPublishers > 0 ? (int)ceil($totalPublishers / $this->perFile) : 0;
            for ($i = 1; $i <= $publisherPages; $i++) {
                $loc = URL::to("/sitemaps/publishers-{$i}/file.xml");
                $items .= '<sitemap>'
                        . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                        . '</sitemap>';
            }


            return $this->wrapSitemapIndex($items);
        });

        return $this->xmlResponse($xml);
    }

    public function booksFile(int $n): Response
    {
        if ($n < 1) abort(404);

        $cacheKey = "sitemap.books.file.{$n}";

        $xml = Cache::remember($cacheKey, 86400, function () use ($n) {
            $offset = ($n - 1) * $this->perFile;

            $books = DB::table('products')
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->offset($offset)
                ->limit($this->perFile)
                ->get(['id', 'slug', 'updated_at']);

            if ($books->isEmpty()) abort(404);

            $urls = '';
            foreach ($books as $book) {
                $loc = URL::to('/books/' . $book->slug);
                $urls .= '<url>'
                    . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                    . '<lastmod>'.date("Y-m-d").'</lastmod>'
                    . '<changefreq>weekly</changefreq>'
                    . '</url>';
            }

            return $this->wrapUrlSet($urls);
        });

        return $this->xmlResponse($xml, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }


    public function authorsFile(int $n): Response
    {
        if ($n < 1) abort(404);

        $cacheKey = "sitemap.authors.file.{$n}";

        $xml = Cache::remember($cacheKey, 86400, function () use ($n) {
            $offset = ($n - 1) * $this->perAuthor;

            $authors = DB::table('users')
                ->where('user_type', 2)
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->offset($offset)
                ->limit($this->perAuthor)
                ->get(['id', 'updated_at']);

            if ($authors->isEmpty()) abort(404);

            $urls = '';
            foreach ($authors as $author) {
                // Use your named route to build the correct URL:
                // Route::get('/author/books/{slug}', ...)->name('AuthorBooks');
                $loc = route('AuthorBooks', ['slug' => $author->id], true);
                $urls .= '<url>'
                    . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                    . '<lastmod>'.date("Y-m-d").'</lastmod>'
                    . '<changefreq>weekly</changefreq>'
                    . '</url>';
            }

            return $this->wrapUrlSet($urls);
        });

        return $this->xmlResponse($xml, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function publishersFile(int $n): Response
    {
        if ($n < 1) abort(404);

        $cacheKey = "sitemap.publishers.file.$n";
        $xml = Cache::remember($cacheKey, 86400, function () use ($n) {
            $offset = ($n - 1) * $this->perPublisher;

            $publishers = DB::table('brands')
                ->where('status', 1)
                ->orderBy('id', 'asc')
                ->offset($offset)->limit($this->perPublisher)
                ->get(['id', 'slug', 'updated_at']);

            if ($publishers->isEmpty()) abort(404);

            $urls = '';
            foreach ($publishers as $publisher) {
                // Use your named route: /publisher/books/{slug}  name: PublisherBooks
                $loc = route('PublisherBooks', ['slug' => $publisher->slug], true);

                $urls .= '<url>'
                    . '<loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc>'
                    . '<lastmod>'.date("Y-m-d").'</lastmod>'
                    . '<changefreq>weekly</changefreq>'
                    . '</url>';
            }

            return $this->wrapUrlSet($urls);
        });

        return $this->xmlResponse($xml, ['Cache-Control' => 'public, max-age=86400']);
    }


    private function wrapSitemapIndex(string $inner): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . $inner
            . '</sitemapindex>';
    }

    private function wrapUrlSet(string $inner): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            . $inner
            . '</urlset>';
    }

    private function xmlResponse(string $xml, array $extraHeaders = []): Response
    {
        $headers = array_merge([
            'Content-Type' => 'application/xml; charset=UTF-8',
            // Robots header is optional; Google ignores it for sitemaps anyway.
        ], $extraHeaders);

        // Optional: ETag for extra cache friendliness
        $etag = '"' . md5($xml) . '"';
        $headers['ETag'] = $etag;

        return response($xml, 200, $headers);
    }
}
