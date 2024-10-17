<?php

namespace Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Directory extends Model implements Auditable
{
    //
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'directory_name', 
        'directory_name_ar', 
        'directory_path', 
        'parent_directory_id',
        'directory_owner_id',
        'order',
        'organization_id'
    ];


    public static function rules($action, $id = null) {
        switch ($action) {
            case 'save':
                return array(
                    'directory_path' => 'required',
                    'directory_owner_id' => 'required',
                    'directory_name' => 'required',
                );
            case 'update':
                return array(
                    'directory_path' => 'required',
                    'directory_owner_id' => 'required',
                    'directory_name' => 'required',
                );
        }
    }

    public function parentDirectory()
    {
        return $this->belongsTo('Models\Directory','parent_directory_id');
    }
    
    public function directoryOwner()
    {
        return $this->belongsTo('Models\User','directory_owner_id');
    }

    public function storageAccess(){
        return $this->hasMany('Models\StorageAccess','directory_id');
    }

    public function files()
    {
        return $this->hasMany('Models\File','directory_id');
    }

    public function childDirectories()
    {
        return $this->hasMany('Models\Directory','parent_directory_id');
    }

    public function parentBreakDowns(){
        return $this->hasMany('Models\DirectoryBreakDown','directory_id');
    }

    public function childBreakDowns(){
        return $this->hasMany('Models\DirectoryBreakDown','parent_id');
    }

    public function directoryUsers(){
        return $this->belongsToMany('Models\User', 'storage_accesses','directory_id', 'user_id')->withPivot('id','can_read','can_edit','can_delete')->orderBy('storage_accesses.created_at','desc');
    }

    public function organization(){
        return $this->belongsTo('Models\Organization','organization_id');
    }

    public function committees()
    {
        return $this->hasMany(Committee::class, 'directory_id');
    }

    protected static function boot() {
        parent::boot();
        
        static::deleting(function($directory) {
            foreach ($directory->childBreakDowns as $breakdown){
                if($breakdown->directory_id != $directory->id){
                    if($breakdown->directory){
                        $breakdown->directory->files()->delete();
                        $breakdown->directory()->delete();
                    }
                    $breakdown->delete();
                }
            }
            $directory->files()->delete();
        });
    }
}
