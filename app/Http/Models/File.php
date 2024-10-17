<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class File extends Model implements Auditable
{
    //
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'file_name',
        'file_name_ar', 
        'file_path', 
        'directory_id',
        'file_owner_id',
        'order',
        'organization_id',
        'file_size',
        'file_type_id',
        'is_system'
    ];

    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'file_name' => 'required',
                    'file_path' => 'required',
                    'file_owner_id' => 'required',
                );
            case 'update':
                return array(
                    'file_name' => 'required',
                    'file_path' => 'required',
                    'file_owner_id' => 'required',
                );
        }
    }


    public function directory()
    {
        return $this->belongsTo('Models\Directory');
    }
    
    public function directoryOwner()
    {
        return $this->belongsTo('Models\User','file_owner_id');
    }

    public function storageAccess(){
        return $this->hasMany('Models\StorageAccess','file_id');
    }

    public function fileUsers(){
        return $this->belongsToMany('Models\User', 'storage_accesses','file_id', 'user_id')->withPivot('id','can_read','can_edit','can_delete')->orderBy('storage_accesses.created_at','desc');
    }

    public function organization(){
        return $this->belongsTo('Models\Organization','organization_id');
    }

    public function fileType(){
        return $this->belongsTo('Models\FileType','file_type_id');
    }

    public function attachmentes(){
        return $this->hasMany('Models\Attachment','file_id');
    }

    public  static function boot() {
        parent::boot();

        static::deleting(function($file) {
            //remove related file
            $file->attachmentes()->delete();//
        });
    }
}
