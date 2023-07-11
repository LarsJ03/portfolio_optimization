<?php namespace Examify\Exams\Models;

use Model;

/**
 * NavbarManager Model
 */
class NavbarManager extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'examify_exams_navbar_managers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    // get the navbar manager
    public static function getNavbarItemsMain($schoolyear = '')
    {

        // default the schoolyear
        $schoolyear = !empty($schoolyear) ? $schoolyear : \Examify\Exams\Models\Classes::getCurrentYear(); 

        // get the user
        $user = Users::getUser();

        // check the rights of the user
        if(!$user)
        {
          $isSuperAdmin = false;
          $isAdmin = false;
          $isSchoolAdmin = false;
          $isTeacher = false;
        }
        else {
          $isSuperAdmin = $user->getUserSetting('isSuperAdmin') ? true : false;
          $isAdmin = $user->getUserSetting('isAdmin') ? true : $isSuperAdmin;
          $isSchoolAdmin = $user->isSchoolAdmin();
          $isTeacher = $user->isTeacher();
        }

        return [
            [
                'url' => '/',
                'name' => 'Home',
                'show' => true,
            ],
            [
                'url' => '/bestellen',
                'name' => 'Bestellen',
                'show' => false
            ],
            [
                'url' => '/oefenen',
                'name' => 'Oefenen',
                'show' => true,
                'activeurl' => '/oefenen'
            ],
            [
                'url' => '/theorie',
                'name' => 'Theorie',
                'show' => true,
                'activeurl' => '/theorie'
            ],
            [
                'url' => '/huiswerk/',
                'name' => 'Huiswerk',
                'show' => !$isTeacher
            ],
            [
                'url' => '/portal/classes/' . $schoolyear,
                'name' => 'Mijn klassen',
                'show' => $isTeacher,
                'activeurl' => '/portal/classes'
            ],
            [
                'url' => '/portal/licences',
                'name' => 'Portaal',
                'show' => $isSchoolAdmin,
                'activeurl' => '/portal'
            ],
            [
                'url' => '/over-ons',
                'name' => 'Over ons',
                'show' => true
            ],
            [
                'url' => '/mijn-account',
                'name' => 'Mijn account',
                'show' => !!$user,
                'extracss' => 'd-none d-lg-block'
            ],
            [
                'url' => '/login',
                'name' => 'Inloggen',
                'show' => !$user,
                'extracss' => 'd-lg-none'
            ]
        ];
    }
}
