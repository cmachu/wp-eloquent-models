<?php

namespace WPEloquent\Model;

use WPEloquent\Traits\HasMeta;

class Post extends BaseModel
{
    use HasMeta;

    /** @var string */
    protected $table      = 'posts';
    
    /** @var string */
    protected $primaryKey = 'ID';
    
    /** @var string|null */
    protected $post_type = null;

    /** @var string */
    public const CREATED_AT = 'post_date';
    
    /** @var string */
    public const UPDATED_AT = 'post_modified';

    /** @var array */
    protected $fillable = [
        'ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status',
        'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified',
        'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type',
        'post_mime_type', 'comment_count'
    ];

    public function newQuery()
    {
        $query = parent::newQuery();
        if ($this->post_type) {
            return $this->scopeType($query, $this->post_type);
        }
        return $query;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function author()
    {
        return $this->hasOne(User::class, 'ID', 'post_author');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function meta()
    {
        return $this->hasMany(Post\Meta::class, 'post_id')
                    ->select(['post_id', 'meta_key', 'meta_value']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function terms()
    {
        return $this->hasManyThrough(
            Term\Taxonomy::class,
            Term\Relationships::class,
            'object_id',
            'term_taxonomy_id'
        )->with('term');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function categories()
    {
        return $this->terms()->where('taxonomy', 'category');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'post_parent', 'ID')
                    ->where('post_type', 'attachment');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function tags()
    {
        return $this->terms()->where('taxonomy', 'post_tag');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'comment_post_ID');
    }

    public function scopeStatus($query, $status = 'publish')
    {
        return $query->where('post_status', $status);
    }

    /**
     * @return self
     */
    public function scopeType($query, $type = 'post')
    {
        return $query->where('post_type', $type);
    }

    /**
     * @return self
     */
    public function scopePublished($query)
    {
        return $query->status('publish');
    }
}
