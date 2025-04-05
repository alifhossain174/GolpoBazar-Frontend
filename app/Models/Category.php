<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public static function getDropDownList($fieldName, $id=NULL, $is_audio=NULL){
        $str = "<option value=''>Select One</option>";

        $query = self::orderBy($fieldName,'asc')->where('status', 1);
        if($is_audio != NULL){
            $query->where('is_audio', $is_audio);
        }
        $lists = $query->get();

        if($lists){
            foreach($lists as $list){
                if($id !=NULL && $id == $list->id){
                    $str .= "<option  value='".$list->id."' selected>".$list->$fieldName."</option>";
                }else{
                    $str .= "<option  value='".$list->id."'>".$list->$fieldName."</option>";
                }

            }
        }
        return $str;
    }

}
