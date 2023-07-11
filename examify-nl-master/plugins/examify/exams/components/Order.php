<?php namespace Examify\Exams\Components;

use Cms\Classes\ComponentBase;
use \Examify\Exams\Models\Courses as Courses;
use \Examify\Exams\Models\Classes as Classes;
use \Examify\Exams\Classes\Pricing as Pricing;
use \Examify\Exams\Models\Users as Users;
use \Examify\Exams\Components\Account as Account;
use \Examify\Exams\Models\Orders as Orders;
use \Examify\Exams\Models\Vouchers as Vouchers;
use Carbon\Carbon;

class Order extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Order Component',
            'description' => 'Order the products available within Examify'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function getPackagePrices()
    {
        return [
           1 => Pricing::getPrice(3),
           2 => Pricing::getPrice(2),
           3 => Pricing::getPrice(1)
        ];
    }

    public function onRender()
    {
        $this->page['packageprices'] = $this->getPackagePrices();
    }

    public function onSelectPackage()
    {
        // get the package nr
        $packageid = input('packageid', false);

        if(!$packageid)
        {
            return [
                'valid' => false
            ];
        }

        $phase = 1;

        // show the selected package
        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $phase, 
                    $this->renderPartial('order/select-level', [
                        'orderinformation' => [
                            'phase' => $phase,
                            'packageid' => $packageid
                        ]
                    ])
                ]
            ]
        ];
    }

    private function getPackageOneOptions()
    {

        // get the orderinformation
        $orderinformation = $this->getOrderInformation(['level', 'packageid', 'phase']);

        if(!$orderinformation || $orderinformation['packageid'] != 1)
        {
            return [ 'valid' => false ];
        }

        // get all the available levels for sale
        $courses = Courses::where('for_sale', 1)->where('name', '!=', 'Nederlands')->where('name', '!=', 'Engels')->where('level', $orderinformation['level'])->get();
        if(!$courses->count())
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldig niveau.'
            ];
        }

        $orderinformation['courses'] = $courses;

        // generate the next step, and get the courses that are available
        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'], 
                    $this->renderPartial('order/package-1-select-extra-course', [
                        'orderinformation' => $orderinformation
                    ])
                ]
            ]
        ];
    }

    public function getPackageTwoOptions()
    {
        $level = input('level', false);
        $packageid = input('packageid', false);
        $phase = input('phase', false);

        $orderinformation = $this->getOrderInformation(['level', 'packageid', 'phase']);

        if(!$orderinformation || $orderinformation['packageid'] != 2)
        {
            return [ 'valid' => false ];
        }

        $courses = [
            Courses::where('name', 'Nederlands')->where('level', $level)->first(),
            Courses::where('name', 'Engels')->where('level', $level)->first(),
        ];

        // show the years now
        return $this->getYearOptions($courses);

    }

    public function getPackageThreeOptions()
    {
        $level = input('level', false);
        $packageid = input('packageid', false);
        $phase = input('phase', false);

        if(!$phase || !$packageid || $packageid != 3 || !$level)
        {
            return [ 'valid' => false ];
        }

        // get all the available courses
        $courses = Courses::where('for_sale', 1)->where('level', $level)->get();

        if(!$courses)
        {
            return [
                'valid' => false,
                'message' => 'Er zijn geen vakken te koop voor dit niveau.'
            ];
        }

        for($ii = 0; $ii < $courses->count(); $ii++)
        {
            $prices[] = Pricing::getPrice($ii + 1);
        }

        // generate the next step, and get the courses that are available
        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $phase,
                    $this->renderPartial('order/package-3-select-courses', [
                        'orderinformation' => [
                            'courses' => $courses,
                            'level' => $level,
                            'phase' => $phase,
                            'packageid' => $packageid,
                            'prices' => $prices,
                            'pricepercourse' => Pricing::getPrice(1)
                        ]
                    ])
                ]
            ]
        ];
    }

    public function onSelectLevel()
    {
        // get the level
        $level = input('level', false);
        $packageid = input('packageid', false);

        if(!$packageid || !$level)
        {
            return [
                'valid' => false
            ];
        }

        // in case of package 1, return those options
        if($packageid == 1){ return $this->getPackageOneOptions(); }
        if($packageid == 2){ return $this->getPackageTwoOptions(); }
        if($packageid == 3){ return $this->getPackageThreeOptions(); }

    }

    private function getYearOptions($courses)
    {

        $orderinformation = $this->getOrderInformation(['phase']);

        if(!$orderinformation){
            return [ 'valid' => false ];
        }

        $currentyear = Classes::getCurrentYear();

        $orderinformation['courses'] = $courses;
        $orderinformation['years'] = [
            $currentyear => 'Vanaf nu tot en met 31 juli ' . $currentyear,
            $currentyear+1 => 'Vanaf 1 augustus ' . $currentyear . ' tot en met 31 juli ' . ($currentyear+1)
        ];

        $orderinformation['discountForTwoYears'] = Pricing::getDiscountForTwoYears();

        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/select-years', [
                        'orderinformation' => $orderinformation
                    ])
                ]
            ]
        ];
    }

    public function onSelectPackageOneCourse()
    {

        // get the selected coarse ID
        $courseid = input('courseid', false);
        $phase = input('phase', false);

        if(!$courseid || !$phase)
        {
            return [
                'valid' => false,
                'message' => 'Dit is geen geldig vak. Probeer het opnieuw.'
            ];
        }

        // get the course
        $course = Courses::where('id', $courseid)->where('for_sale', 1)->first();
        if(!$course)
        {
            return [
                'valid' => false,
                'message' => 'Dit vak is niet gevonden in ons systeem.'
            ];
        }

        $level = $course->level;

        // get the courses
        $courses = [
            Courses::where('name', 'Nederlands')->where('level', $level)->first(),
            Courses::where('name', 'Engels')->where('level', $level)->first(),
            $course ];

        return $this->getYearOptions($courses);

    }

    public function onSelectPackageThreeCourses()
    {

        $extraData = input('extraData', false);
        $ncourses = input('ncourses', false);
        $courseids = input('courseids', false);

        if($extraData)
        {
            $extraData = json_decode($extraData);
        }

        // in case no extra data is provided, it means the form is submitted
        if(!$extraData)
        {
            if(!$courseids){
                return [
                    'valid' => false,
                    'message' => 'Selecteer minstens 1 vak.'
                ];
            }

            // get the courses the user selected
            $courses = Courses::where('for_sale', 1)->find($courseids);

            return $this->getYearOptions($courses);

        }

        if($extraData){

            // this means the form is just updated
            if(!$courseids){ 
                $nselected = 0;
            }
            else {
                $nselected = count($courseids);
            }

            if($nselected == $ncourses)
            {
                return [
                    'valid' => true,
                    'showElement' => [
                        '#course-confirm',
                        '#confirm-selection'
                    ],
                    'hideElement' => [
                        '#standard-package-price-info',
                        '#extra-course'
                    ]
                ];
            }
            elseif($nselected == 0){
                return [
                    'valid' => true,
                    'showElement' => [
                        '#standard-package-price-info'
                    ],
                    'hideElement' => [
                        '#extra-course',
                        '#course-confirm',
                        '#confirm-selection'
                    ]
                ];
            }
            else{
                // get the extra price 
                $extra = Pricing::getPrice($nselected + 1);
                $base   = Pricing::getPrice($nselected);
                $extra->subtract($base);
                return [
                    'valid' => true,
                    'updateElement' => [
                        '#extra-course-price' => $extra->getDutchFormat()
                    ],
                    'showElement' => [
                        '#extra-course',
                        '#confirm-selection'
                    ],
                    'hideElement' => [
                        '#standard-package-price-info',
                        '#course-confirm'
                    ]
                ];
            }
            
        }


    }

    public function onSelectUserForm()
    {
        $type = input('type', false);

        $orderinformation = $this->getOrderInformation(['phase', 'courseids', 'yearsToOrder']);

        if(!$orderinformation['courses'])
        {
            return [
                'valid' => false,
                'message' => 'Er zijn geen vakken geselecteerd.',
            ];
        }

        // based on the type, return the partial
        if($type != 'login' && $type != 'registreer')
        {
            return [ 
                'valid' => false, 
                'message' => 'Dit is geen geldige keuze.',
                'type' => $type
            ];
        }

        // reduce the phase in the form elements, to make sure that the new boxes are generated in the place of the forms of login and register
        $thisphase = $orderinformation['phase'];
        $orderinformation['phase'] = $thisphase - 1;

        if($type == 'login')
        {
            return [
                'valid' => true,
                'call-js-function' => [
                    'showPhase' => [
                        $thisphase,
                        $this->renderPartial('examifyHelpers/login', [
                            'redirect_to' => false,
                            'orderinformation' => $orderinformation,
                            'requestFunction' => 'onOrderLogin',
                            'hideRegister' => true,
                        ])
                    ]
                ]
            ];
        }

        if($type == 'registreer')
        {
            return [
                'valid' => true,
                'call-js-function' => [
                    'showPhase' => [
                        $thisphase,
                        $this->renderPartial('examifyHelpers/register', [
                            'redirect_to' => false,
                            'requestFunction' => 'onOrderRegister',
                            'hideLogin' => true,
                            'orderinformation' => $orderinformation
                        ])
                    ]
                ]
            ];
        }

    }

    public function onOrderLogin()
    {
        $account = new Account($this->page);
        $response = $account->onSignin();

        // get all the orderinformation that is required
        $orderinformation = $this->getOrderInformation(['phase', 'courseids', 'yearsToOrder']);

        if(!$orderinformation)
        {
            return [
                'allvalid' => false
            ];
        }

        // remove always the redirect
        $reponse['redirect'] = false;

        // update the response with an update and call to the call-js-function
        if($response['allvalid'])
        {
            return $this->getPersonalInformation();
        }

        return $response;
    }

    public function onOrderRegister()
    {
        $account = new Account($this->page);
        $response = $account->onRegister();
        
        // get all the orderinformation that is required
        $orderinformation = $this->getOrderInformation(['phase', 'courseids', 'yearsToOrder']);

        if(!$orderinformation)
        {
            return [
                'allvalid' => false,
                'oi' => input()
            ];
        }

        // remove the redirect anyway
        $reponse['redirect'] = false;

        // update the response with an update and call to the call-js-function
        if($response['allvalid']){
            return $this->getPersonalInformation();
        }

        return $response;

    }

    public function getPersonalInformation()
    {
        // get the orderinformation
        $orderinformation = $this->getOrderInformation(['phase', 'courseids', 'yearsToOrder']);

        $user = Users::getUser();

        if(!$user)
        {
            return [
                'valid' => false,
                'message' => 'Je bent niet aangemeld.'
            ];
        }

        if(!$orderinformation['courses'])
        {
            return [
                'valid' => false,
                'message' => 'Geen van deze vakken zijn te koop.'
            ];
        }

        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/personal-information', [
                        'orderinformation' => $orderinformation,
                        'user' => $user,
                    ])
                ]
            ]
        ];
    }

    public static function validatePersonalInformation()
    {
        // get the contact information
        $street = input('street', false);
        $city = input('city', false);
        $country = input('country', false);
        $zipcode = input('zipcode', false);

        $allvalid = !(empty($street) || empty($city) || empty($country) || empty($zipcode) );

        if($zipcode)
        {
            $zipcode = ucwords($zipcode);

            $valid1 = preg_match('/^[1-9]{1}[0-9]{3} [A-Z]{2}$/', $zipcode);
            $valid2 = preg_match('/^[1-9]{1}[0-9]{3}[A-Z]{2}$/', $zipcode);

            if(!$valid1 && !$valid2){
                $zipcode = false;
                $allvalid = false;
            }
        }
    
        return [
            'allvalid' => $allvalid,
            'inputvalidation' => [
                'street' => [
                    'valid' => !empty($street),
                    'message' => 'Vul een straat in.'
                ],
                'city' => [
                    'valid' => !empty($city),
                    'message' => 'Vul een plaats in.',
                ],
                'country' => [
                    'valid' => !empty($country),
                    'message' => 'Vul een land in.',
                ],
                'zipcode' => [
                    'valid' => !empty($zipcode),
                    'message' => 'Vul een geldige postcode in (1234 AB).'
                ]
            ]
        ];
    }

    public function onConfirmPersonalInformation()
    {
        
        $user = Users::getUser();

        if(!$user)
        {
            $this->getUserLogin();
        }

        // check if the contact information is there
        $contactinfo = input('contactinfo', false);
        if(!$contactinfo)
        {
            return $this->getPersonalInformation();
        }

        $check = $this->validatePersonalInformation();
        if(!$check['allvalid']){
            return $check;
        }

        $orderinformation = $this->getOrderInformation(['courseids', 'phase', 'yearsToOrder', 'street', 'zipcode', 'city', 'country']);

        if(!$orderinformation)
        {
            return [ 'valid' => false, 'message' => 'De orderinformation is niet geldig.']; 
        }

        if(!$orderinformation['courses'])
        {
            return [
                'valid' => false,
                'message' => 'Voor dit vak kan op dit moment geen licentie aangeschaft worden.'
            ];
        }

        // update the settings
        $user->setUserSetting('street', $orderinformation['street']);
        $user->setUserSetting('zipcode', $orderinformation['zipcode']);
        $user->setUserSetting('city', $orderinformation['city']);
        $user->setUserSetting('country', $orderinformation['country']);

        // show the voucher option
        return $this->onShowVoucher();

    }

    public function onConfirmVoucher()
    {
        return $this->onShowOverview();
    }

    public function onSelectYears()
    {
        $orderinformation = $this->getOrderInformation(['courseids', 'phase', 'yearsToOrder']);

        if(!$orderinformation)
        {
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 schooljaar.'
            ];
        }

        // validate the years to order
        if(!$this->validateYearsToOrder())
        {
            return [
                'valid' => false,
                'message' => 'Er is iets fout gegaan. Dit is geen geldige keuze meer.'
            ];
        }

        // continue with the personal information
        return $this->getUserLogin();

    }

    public function getUserLogin()
    {

        // in case the user is already logged in, show the personal information
        $user = Users::getUser();
        if($user)
        {
            return $this->getPersonalInformation();
        }

        $orderinformation = $this->getOrderInformation(['courseids', 'phase', 'yearsToOrder']);

        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/user-form', [
                        'orderinformation' => $orderinformation
                    ])
                ]
            ]
        ];
    }

    public function validateYearsToOrder()
    {
        $years = input('yearsToOrder', false);
        if(!$years){ return false; }

        if(count($years) == 2){ return true; }
        if(count($years) == 1)
        {
            $currentyear = Classes::getCurrentYear();
            return ($years[0] == $currentyear || $years[0] == ($currentyear + 1));
        }
    }

    public function getCoursesToLicence($courseids, $yearsToOrder, $voucherid)
    {
        $user = Users::getUser();
        if(!$user)
        {
            return false;
        }

        $currentyear = Classes::getCurrentYear();
        $years = [
            $currentyear,
            $currentyear+1
        ];

        $result = [];

        foreach($years as $year)
        {
            $result[$year]['courseids'] = [];
            $result[$year]['active'] = $active = in_array($year, $yearsToOrder);

            // loop over the courses
            if(!$active){
                continue;
            }

            foreach($courseids as $courseid)
            {
                if(!$user->hasLicenceForCourseIdAndYear($courseid, $year)){
                    $result[$year]['courseids'][] = $courseid;
                }
            }

            // get the price 
            $result[$year]['baseprice'] = Pricing::getPrice(count($result[$year]['courseids']));
            $result[$year]['price'] = $result[$year]['baseprice'];
        }

        // determine which year gets discount
        if($result[$years[0]]['active'] && $result[$years[1]]['active']){
            $price0 = $result[$years[0]]['baseprice'];
            $price1 = $result[$years[1]]['baseprice'];

            $discountpercentage = Pricing::getDiscountForTwoYears();

            if($price0->value >= 0.01 && $price1->value >= 0.01)
            {
                if($price1->value <= $price0->value)
                {
                    $result[$years[1]]['discount'] = true;
                    $newprice = new Pricing($price1->value);
                    $newprice->applyDiscountPercentage($discountpercentage);
                    $result[$years[1]]['discountAmount'] = $price1->getDiscountForPercentage($discountpercentage);
                    $result[$years[1]]['price'] = $newprice;
                }
                else {
                    $result[$years[0]]['discount'] = true;
                    $newprice = new Pricing($price0->value);
                    $newprice->applyDiscountPercentage($discountpercentage);
                    $result[$years[0]]['discountAmount'] = $price0->getDiscountForPercentage($discountpercentage);
                    $result[$years[0]]['price'] = $newprice;
                }
            }
        }

        // define the total price to pay
        $topay = new Pricing(0.00);
        $nlines = 0;
        $activeElements = 0;
        foreach($years as $year)
        {
            if($result[$year]['active'])
            {
                $topay->add($result[$year]['price']);

                // sum the number of lines
                $nlines++;

                // sum the number of active elements
                if(!empty($result[$year]['courseids']))
                {
                    $activeElements++;
                }
            }
        }

        $beforediscount = false;

        // get the voucher id
        if($voucherid)
        {
            // get the voucher
            $voucher = Vouchers::find($voucherid);
            if($voucher->isActive() != 1)
            {
                return [
                    'valid' => false,
                    'message' => 'Deze voucher is niet meer geldig. Probeer opnieuw.'
                ];
            }

            // check if it is euro discount or percentage of total price
            if($voucher->discount_perc)
            {
                $vdiscount = $topay->getDiscountForPercentage($voucher->discount_perc);
            }

            if($voucher->discount_eur)
            {
                $vdiscount = new Pricing(min($voucher->discount_eur, $topay->value));
            }

            // update the topay
            $newprice = new Pricing($topay);
            $newprice->subtract($vdiscount);
            $beforediscount = $topay;
            $topay = $newprice;
        }
        else {
            $vdiscount = new Pricing(0.00);
            $voucher = false;
        }

        // check if it is defined.
        return [
            'topay' => $topay,
            'years' => $result,
            'nlines' => $nlines,
            'beforediscount' => $beforediscount,
            'vdiscount' => new Pricing(-$vdiscount->value),
            'voucher' => $voucher,
            'activeElements' => $activeElements
        ];
    }

    public function onShowOverview()
    {

        $user = Users::getUser();

        if(!$user)
        {
            $this->getUserLogin();
        }

        $orderinformation = $this->getOrderInformation(['courseids', 'phase', 'yearsToOrder', 'street', 'zipcode', 'city', 'country', 'voucher_code']);

        if(!$orderinformation)
        {
            return [ 'valid' => false, 'message' => 'De orderinformation is niet geldig.']; 
        }

        if(!$orderinformation['courses'])
        {
            return [
                'valid' => false,
                'message' => 'Voor dit vak kan op dit moment geen licentie aangeschaft worden.'
            ];
        }

        $courses = $orderinformation['courses'];

        $years = $orderinformation['yearsToOrder'];

        if(!$years)
        {
            return [
                'valid' => false,
                'message' => 'Selecteer minstens 1 schooljaar.'
            ];
        }

        // get the number of years to buy
        $currentyear = Classes::getCurrentYear();

        // remove the already existing licences
        $pricing = $this->getCoursesToLicence($orderinformation['courseids'], $orderinformation['yearsToOrder'], $orderinformation['voucherid']);

        $orderinformation['discountForTwoYears'] = Pricing::getDiscountForTwoYears();
        $orderinformation['currentyear'] = $currentyear;
        $orderinformation['pricing'] = $pricing;
        $orderinformation['topay'] = $pricing['topay'];

        return [
            'allvalid' => true,
            'inputvalidation' => [
                'voucher_code' => [
                    'valid' => true,
                    'message' => 'Toegevoegd'
                ]
            ],
            'update_form' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/show-overview', [
                        'orderinformation' => $orderinformation,
                        'user' => $user,
                    ])
                ]
            ]
        ];

    }

    public function onWithoutVoucher()
    {
        return $this->onShowOverview();
    }

    public function onAddVoucher()
    {
        // check if the voucher code
        $code = input('voucher_code', []);

        if(empty($code))
        {
            return [
                'valid' => false,
                'inputvalidation' => [
                    'voucher_code' => [
                        'valid' => false,
                        'message' => 'Vul een kortingscode in'
                    ]
                ]
            ];
        }

        // validate the code
        $voucher = Vouchers::where('code', $code)->first();

        if(!$voucher)
        {
            return [
                'valid' => false,
                'inputvalidation' => [
                    'voucher_code' => [
                        'valid' => false,
                        'message' => 'Deze code komt ons niet bekend voor.'
                    ]
                ]
            ];
        }

        // validate that the voucher is still active
        if($voucher->isActive() != 1)
        {

            switch ($voucher->isActive()) {
                case -1:
                    $message = 'Deze code is nog niet geldig.';
                    break;

                case -2:
                    $message = 'Deze code is niet meer geldig.';
                    break;

                case -3:
                    $message = 'Deze code kan niet meer gebruikt worden';
                    break;
                
                default:
                    # code...
                    break;
            }

            return [
                'valid' => false,
                'inputvalidation' => [
                    'voucher_code' => [
                        'valid' => false,
                        'message' => $message
                    ]
                ]
            ];
        }



        return $this->onShowOverview();

    }

    public function onShowVoucher()
    {

        $user = Users::getUser();

        if(!$user)
        {
            $this->getUserLogin();
        }


        $check = $this->validatePersonalInformation();

        $orderinformation = $this->getOrderInformation(['courseids', 'phase', 'yearsToOrder', 'street', 'zipcode', 'city', 'country']);

        return [
            'allvalid' => true,
            'inputvalidation' => $check['inputvalidation'],
            'update_form' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/voucher-form', [
                        'orderinformation' => $orderinformation,
                        'user' => $user,
                    ])
                ]
            ]
        ];

    }

    public function onOrder()
    {

        $user = Users::getUser();

        if(!$user)
        {
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd. Log eerst in en probeer dan opnieuw.'
            ];
        }

        // get all the order information
        $orderinformation = $this->getOrderInformation(['courseids', 'topay', 'yearsToOrder', 'street', 'zipcode', 'city', 'country', 'phase', 'voucher_code']);

        if(!$orderinformation)
        {
            return [
                'valid' => false,
                'message' => 'Er is iets verkeerd gegaan. Probeer opnieuw.',
            ];
        }

        // check if topay is not 0
        $topay = $orderinformation['topay'];
        if($topay->value == 0.00)
        {
            // check it, and create the licenses
            $orderinformation = $this->getOrderInformation(['courseids', 'topay', 'yearsToOrder', 'street', 'zipcode', 'city', 'country', 'phase', 'voucher_code']);

            $pricing = $this->getCoursesToLicence($orderinformation['courseids'], $orderinformation['yearsToOrder'], $orderinformation['voucherid']);

            $topay = $pricing['topay'];

            if($topay->value > 0.00)
            {
                return [
                    'valid' => false,
                    'message' => 'Er is iets mis gegaan. De bestelling lijkt niet meer up-to-date. Probeer het opnieuw.'
                ];
            }

            // create the order
            $order = new Orders();
            $order->user_id = $user->id;
            $order->courses = $orderinformation['courses'];
            $order->pricing = $pricing;
            $order->voucher_id = $orderinformation['voucherid'];
            $order->save();

            // get the lines from pricing
            $lines = $this->getLinesFromPricing($pricing, $orderinformation);
            $order->lines = $lines;

            $order->name = Carbon::now()->format('Ymd') . '-' . sprintf('%05d', $order->id);
            $order->status = 'paid';
            $order->mollie_id = 'DISCOUNT';
            $order->save();

            // activate the licenses and generate the invoice
            $order->activateLicences();
            $order->generateInvoice();

            // return that all is fine, and redirect to the shopcart
            return [
                'valid' => true,
                'redirect' => [
                    'location' => env('MOLLIE_WEBHOOK_BASE', 'https://www.examify.nl') . '/redirect-payments/' . $order->name
                ]
            ];

        }


        // get the payment methods, and show the banks
        $method = mollie()->methods()->get(\Mollie\Api\Types\PaymentMethod::IDEAL, ["include" => "issuers"]);
        return [
            'valid' => true,
            'call-js-function' => [
                'showPhase' => [
                    $orderinformation['phase'],
                    $this->renderPartial('order/select-bank', [
                        'orderinformation' => $orderinformation,
                        'issuers' => $method->issuers
                    ])
                ]
            ]
        ];
    }

    public function getLinesFromPricing($pricing, $orderinformation){
        // get the courses to set the names
        $linename = 'Examify Licenties';
        foreach($orderinformation['courses'] as $course){
            $linename .= ' ' . $course->name . ' (' . $course->level . '),';
        }
        $linename .= ' Schooljaar ';

        // loop over the active courses
        foreach($pricing['years'] as $key => $year)
        {
            if($year['active']){
                $baseprice = $year['baseprice'];
                $price = $year['price'];
                $thisline = [
                    'type' => 'digital',
                    'quantity' => 1,
                    'name' => $linename . ' ' . ($key - 1) . ' - ' . $key,
                    'unitPrice' => [
                        'currency' => "EUR",
                        'value' => $price->getMollieFormat()
                    ],
                    'totalAmount' => [
                        'currency' => "EUR",
                        'value' => $price->getMollieFormat()
                    ],
                    'vatRate' => "0.00",
                    'vatAmount' => [
                        'currency' => "EUR",
                        'value' => "0.00"
                    ]
                ];

                if(!empty($year['discount']))
                {
                    $temp = $year['discountAmount'];
                    $thisline['discountAmount'] = [
                        'currency' => "EUR",
                        'value' => $temp->getMollieFormat()
                    ];
                }

                $lines[] = $thisline;
            }
        }

        // in case the voucher is applied, add a line as GiftCard
        if($pricing['voucher'])
        {
            $voucher = $pricing['voucher'];
            $vdiscount = $pricing['vdiscount'];
            $lines[] = [
                'name' => $voucher->code . ' kortingscode (' . $voucher->getDiscountText() . ')',
                'type' => 'discount',
                'unitPrice' => [
                    'currency' => 'EUR',
                    'value' => $vdiscount->getMollieFormat()
                ],
                'vatRate' => '0.00',
                'quantity' => 1,
                'totalAmount' => [
                    'currency' => 'EUR',
                    'value' => $vdiscount->getMollieFormat()
                ],
                'vatAmount' => [
                    'currency' => 'EUR',
                    'value' => '0.00'
                ]
            ];
        }

        return $lines;
    }

    public function onSelectBank()
    {

        $user = Users::getUser();

        if(!$user)
        {
            return [
                'valid' => false,
                'message' => 'Je bent niet meer ingelogd. Log eerst in en probeer dan opnieuw.'
            ];
        }

        $orderinformation = $this->getOrderInformation(['courseids', 'topay', 'yearsToOrder', 'street', 'zipcode', 'city', 'country', 'phase', 'bank_id', 'voucher_code']);

        if(!$orderinformation)
        {
            return [
                'valid' => false,
                'message' => 'Er is iets verkeerd gegaan. Probeer opnieuw.',
                'input' => input()
            ];
        }

        $pricing = $this->getCoursesToLicence($orderinformation['courseids'], $orderinformation['yearsToOrder'], $orderinformation['voucherid']);
        $topay = $orderinformation['topay'];

        // double check if the topay is still the same
        if($pricing['topay']->value != $topay->value)
        {
            return [
                'valid' => false,
                'message' => 'Er is iets mis gegaan. Veelkans is je bestelling niet meer up-to-date. Refresh de pagina en probeer opnieuw.',
                'pr' => $pricing,
                'tp' => $topay
            ];
        }

        if($topay->value == 0.00)
        {
            return [
                'valid' => false,
                'message' => 'Je hoeft niets te betalen. De totaalprijs is 0.00 euro.'
            ];
        }

        // store the orderinformation in the pricing
        $pricing['orderinformation'] = $orderinformation;

        // generate the order
        $order = new Orders();
        $order->user_id = $user->id;
        $order->courses = $orderinformation['courses'];
        $order->pricing = $pricing;
        $order->voucher_id = $orderinformation['voucherid'];
        $order->save();

        // generate the name based on the date
        $order->name = Carbon::now()->format('Ymd') . '-' . sprintf('%05d', $order->id);
        $order->save();

        $lines = $this->getLinesFromPricing($pricing, $orderinformation);
        $order->lines = $lines;

        // all information is there, create the payment
        $mollieorder = mollie()->orders()->create([
            "amount" => [
                "currency" => "EUR",
                "value" => $topay->getMollieFormat() // You must send the correct number of decimals, thus we enforce the use of strings
            ],
            'billingAddress' => [
                'streetAndNumber' => $orderinformation['street'],
                'postalCode' => $orderinformation['zipcode'],
                'givenName' => $user->name,
                'familyName' => $user->surname,
                'email' => $user->email,
                'city' => $orderinformation['city'],
                'country' => "NL"
            ],
            'expiresAt' => Carbon::now()->addDay()->format('Y-m-d'),
            'orderNumber' => $order->name,
            'lines' => $lines,
            'locale' => 'nl_NL',
            'method' => 'ideal',
            'payment' => [
                'issuer' => $orderinformation['bank_id']
            ],
            "redirectUrl" => env('MOLLIE_WEBHOOK_BASE', 'https://www.examify.nl') . '/redirect-payments/' . $order->name,
            "webhookUrl" => env('MOLLIE_WEBHOOK_BASE', 'https://www.examify.nl') . '/exawebhooks/mollie',
            "metadata" => [
                'orderid' => $order->name,
                'courseids' => $orderinformation['courseids'],
                'yearsToOrder' => $orderinformation['yearsToOrder'],
            ],
        ]);

        // save the id of this order to the mollie_id
        $order->mollie_id = $mollieorder->id;
        $order->save();

        // return the redirect url
        return [
            'valid' => true,
            'redirect' => [
                'location' => $mollieorder->getCheckoutUrl()
            ]
        ];
    }

    public function getOrderInformation($properties)
    {
        foreach($properties as $property)
        {

            $thisvalue = input($property, '_______NOT_DEFINED______');

            if($thisvalue == '_______NOT_DEFINED______'){
                return false;
            }

            if($property == 'courseids')
            {
                $orderinformation['courses'] = Courses::where('for_sale', 1)->find($thisvalue);
            }

            if($property == 'topay')
            {
                $orderinformation['topay'] = new Pricing((float)$thisvalue);
                continue;
            }

            if($property == 'voucher_code')
            {
                // in case it is equal to no voucher input
                if($thisvalue == '__NO_VOUCHER_CODE_INPUT__'){
                    $orderinformation['voucherid'] = 0;
                }
                else {
                    // validate the voucher
                    $voucher = Vouchers::where('code', $thisvalue)->first();
                    if($voucher->isActive() == 1)
                    {
                        $orderinformation['voucherid'] = $voucher->id;
                    }
                    else {
                        $orderinformation['voucherid'] = 0;
                    }
                }
            }

            $orderinformation[$property] = $thisvalue;
        }

        return $orderinformation;
    }

}
