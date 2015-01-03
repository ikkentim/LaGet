<?php namespace LaGet\NuGet;

class PackageRepository
{
    /**
     * @var array
     */
    public static $fieldMappings = [
        'Version' => ['field' => 'version'],
        'Title' => ['field' => 'title'],
        'Dependencies' => ['field' => 'dependencies'],
        'LicenseUrl' => ['field' => 'license_url'],
        'Copyright' => ['field' => 'copyright'],
        'DownloadCount' => ['field' => 'download_count', 'type' => 'Edm.Int32'],
        'ProjectUrl' => ['field' => 'project_url'],
        'RequireLicenseAcceptance' => ['field' => 'require_license_acceptance', 'type' => 'Edm.Boolean'],
        'GalleryDetailsUrl' => ['function' => 'getGalleryUrl'],
        'Description' => ['field' => 'description'],
        'ReleaseNotes' => ['field' => 'release_notes'],
        'PackageHash' => ['field' => 'hash'],
        'PackageHashAlgorithm' => ['field' => 'hash_algorithm'],
        'PackageSize' => ['field' => 'size', 'type' => 'Edm.Int64'],
        'Published' => ['field' => 'created_at', 'type' => 'Edm.DateTime'], //@todo
        'Tags' => ['fields' => 'tags'], //@todo
        'IsLatestVersion' => ['field' => 'is_latest_version', 'type' => 'Edm.Boolean', 'isFilterable' => true],
        'IsPrerelease' => ['field' => 'is_prerelease', 'type' => 'Edm.Boolean', 'isFilterable' => true],
        'VersionDownloadCount' => ['field' => 'version_download_count', 'type' => 'Edm.Int32'],
        'Summary' => ['field' => 'summary'],
        'IsAbsoluteLatestVersion' => ['field' => 'is_absolute_latest_version', 'type' => 'Edm.Boolean', 'isFilterable' => true], //@todo
        'Listed' => ['field' => 'is_listed', 'type' => 'Edm.Boolean'],
        'IconUrl' => ['field' => 'icon_url'],
        'Language' => ['field' => 'language'],
        //@todo ReportAbuseUrl, MinClientVersion, LastEdited, LicenseNames, LicenseReportUrl
    ];

    public static function getAllProperties()
    {
        return array_keys(self::$fieldMappings);
    }
    public static function getMapping($property)
    {
        return self::isProperty($property) ? self::$fieldMappings[$property] : null;
    }

    public static function isProperty($property)
    {
        return array_has(self::$fieldMappings, $property);
    }

    private static function applyFilter($builder, $filter)
    {
        if (!array_has(self::$fieldMappings, $filter))
            return $builder;

        $mapping = self::$fieldMappings[$filter];

        if (array_has($mapping, 'isFilterable') && $mapping['isFilterable'] === true)
            return $builder->where($mapping['field'], true);

        return $builder;
    }

    private static function applyOrder($builder, $order)
    {
        $parts = explode(' ', $order, 2);

        $field = $parts[0];
        $order = count($parts) < 2 ? 'desc' : $parts[1];

        if (!array_has(self::$fieldMappings, $field))
            return $builder;

        $mapping = self::$fieldMappings[$field];

        return $builder->orderBy($mapping['field'], $order);
    }

    public static function castType($field, $value)
    {
        $mapping = self::$fieldMappings[$field];

        if ($value === null)
            return null;

        if (!array_has($mapping, 'type'))
            return (string)$value;

        switch ($mapping['type']) {
            case 'Edm.DateTime':
                return $value->format('Y-m-d\TH:i:s.000\Z');
            case 'Edm.Boolean':
                return $value == true ? 'true' : 'false';
            default:
                return $value;
        }
    }

    public static function query($filter, $orderBy, $top, $skip)
    {
        $builder = \NuGetPackageRevision::whereRaw('1=1');

        if (!empty($filter))
            foreach (explode(',', $filter) as $filterElement)
                $builder = self::applyFilter($builder, $filterElement);

        if (!empty($orderBy))
            foreach (explode(',', $orderBy) as $order)
                $builder = self::applyOrder($builder, $order);

        if (!empty($top))
            $builder = $builder->take($top);

        if (!empty($skip))
            $builder = $builder->skip($skip);

        return $builder;
    }
}