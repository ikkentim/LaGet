<?php

//https://nuget.codeplex.com/SourceControl/latest#src/Server/DataServices/Package.cs
class NuGetPackageRevision extends Eloquent
{
    protected $table = 'nuget_package_revisions';

    protected $fillable = [
        'path', /* string */
        'package_id', /* string */
        'version', /* string */
        'is_prerelease', /* boolean */
        'title', /* string */
        'authors', /* string */
        'owners', /* string */
        'icon_url', /* string */
        'license_url', /* string */
        'project_url', /* string */
        'download_count', /* integer */
        'require_license_acceptance', /* boolean */
        'development_dependency', /* boolean */
        'description', /* string */
        'summary', /* string */
        'release_notes', /* string */
        'published_date', /* calculatable */ /* date */
        //'last_updated_date', /* eloquent field */ /* date */
        'dependencies', /* string */
        'hash', /* string */
        'hash_algorithm', /* string */
        'size', /* long integer */
        'copyright', /* string */
        'tags', /* string */
        'is_absolute_latest_version', /* boolean */
        'is_latest_version', /* boolean */
        'is_listed', /* boolean */
        'version_download_count', /* integer */
        'min_client_version', /* string */
        'language', /* string */
        'user_id', /* uploader, relation */
    ];

    public function user()
    {
        return $this->belongsTo('User');
    }
}