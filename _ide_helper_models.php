<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExcelFormat> $excelFormats
 * @property-read int|null $excel_formats_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MappingConfiguration> $mappingConfigurations
 * @property-read int|null $mapping_configurations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterData> $masterData
 * @property-read int|null $master_data_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UploadHistory> $uploadHistories
 * @property-read int|null $upload_histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $format_name
 * @property string $format_code
 * @property string|null $description
 * @property array<array-key, mixed> $expected_columns
 * @property string $target_table
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MappingConfiguration> $mappingConfigurations
 * @property-read int|null $mapping_configurations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UploadHistory> $uploadHistories
 * @property-read int|null $upload_histories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereExpectedColumns($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereFormatCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereFormatName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereTargetTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExcelFormat whereUpdatedAt($value)
 */
	class ExcelFormat extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $excel_format_id
 * @property string $mapping_index
 * @property array<array-key, mixed> $column_mapping
 * @property array<array-key, mixed>|null $transformation_rules
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\ExcelFormat $excelFormat
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UploadHistory> $uploadHistories
 * @property-read int|null $upload_histories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereColumnMapping($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereExcelFormatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereMappingIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereTransformationRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MappingConfiguration whereUpdatedAt($value)
 */
	class MappingConfiguration extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\UploadHistory|null $uploadHistory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterData query()
 */
	class MasterData extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $track_id
 * @property string $track_name
 * @property string $artist_id
 * @property string $artist_name
 * @property string|null $album_name
 * @property string|null $genre
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property numeric|null $track_price
 * @property numeric|null $collection_price
 * @property string|null $country
 * @property int|null $upload_history_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\UploadHistory|null $uploadHistory
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereAlbumName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereArtistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereArtistName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCollectionPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereGenre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereReleaseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTrackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTrackName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereTrackPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Track whereUploadHistoryId($value)
 */
	class Track extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $excel_format_id
 * @property int|null $mapping_configuration_id
 * @property string $original_filename
 * @property string $stored_filename
 * @property int $total_rows
 * @property int $success_rows
 * @property int $failed_rows
 * @property array<array-key, mixed>|null $error_details
 * @property string $status
 * @property \Illuminate\Support\Carbon $uploaded_at
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\ExcelFormat $excelFormat
 * @property-read \App\Models\MappingConfiguration|null $mappingConfiguration
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereErrorDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereExcelFormatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereFailedRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereMappingConfigurationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereStoredFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereSuccessRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereTotalRows($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadHistory whereUploadedBy($value)
 */
	class UploadHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int|null $department_id
 * @property bool $is_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UploadHistory> $uploadHistories
 * @property-read int|null $upload_histories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

