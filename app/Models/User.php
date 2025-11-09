<?php

namespace App\Models;

use App\Traits\Uuid;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use Uuid;
    use HasTeams;
    use TwoFactorAuthenticatable;

    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $casts = [
        'id' => 'string'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'telepon',
        'brand_id',
        'username',
        'password',
        'bod',
        'gender',
        'profile_photo_path',
        'created_by',
        'dark_mode',
        'uid',
        'sales_channel',
        'status',
        'rate_limit',
        'sync_gp',
        'status_gp',
        'appendix',
        'checkbook_id',
        'referal_code',
        'referal_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
        'role',
        'menus',
        'menu_data',
        'menu_id',
        'notification_count',
        'notification',
        // 'cart',
        'status_agent',
        'created_by_name',
        'service_ginee_url',
        'deposit',
        'company_name',
        'account_id',
        'sales_channels',
        'amount_detail',
        'amount_can_withdraw',
        'brand_ids',
        'company_id'
    ];

    // protected $with = ['brands'];

    /**
     * Get the teamUser associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function teamUser()
    {
        return $this->hasOne(TeamUser::class, 'user_id');
    }

    /**
     * Get the checkbook that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function checkbook()
    {
        return $this->belongsTo(GpCheckbook::class);
    }

    /**
     * The roles that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getRoleAttribute()
    {
        return $this->roles()->first();
    }

    public function getMenuIdAttribute()
    {
        // REFRESH_MENU
        // $menus = $this->role?->menus()->whereNotNull('parent_id')->pluck('menus.id')->toArray();

        // return $menus;
    }

    public function getMenusAttribute()
    {
        // $menus = $this->role?->menus()->with('children')->where('parent_id')->get();
        // return $menus;
    }

    public function getMenuDataAttribute()
    {
        // $role_id = $this->role?->id;
        // $menus = $this->role?->menus()->where('show_menu', 1)->with('children')->whereHas('roles', function ($query) use ($role_id) {
        //     return $query->where('role_id', $role_id);
        // })->where('parent_id')->orderBy('menu_order', 'ASC')->get();

        // return $menus?->map(function ($menu) {
        //     if (!in_array($menu['menu_route'], ['#', null, ''])) {
        //         $menu['spa_route'] = null;
        //         try {
        //             $route = route($menu['menu_route']);
        //             $menu['menu_url'] = $route;
        //             if (strpos($menu['menu_route'], 'spa') !== false) {
        //                 $menu['spa_route'] = str_replace(env('APP_URL'), '', $route);
        //             }
        //         } catch (\Throwable $th) {
        //             $menu['menu_url'] = '#';
        //             $menu['spa_route'] = null;
        //         }
        //         if ($menu['badge']) {
        //             $menu['badge_count'] = getBadge($menu['badge']);
        //         }
        //     }
        //     foreach ($menu['children'] as $key => $children) {
        //         $menu['children'][$key]['menu_url'] = '#';
        //         $menu['children'][$key]['spa_route'] = null;
        //         if ($children['menu_route']) {
        //             try {
        //                 $route = route($children['menu_route']);
        //                 $menu['children'][$key]['menu_url'] = $route;
        //                 if (strpos($children['menu_route'], 'spa') !== false) {
        //                     $menu['children'][$key]['spa_route'] = str_replace(env('APP_URL'), '', $route);
        //                 }
        //             } catch (\Throwable $th) {
        //                 $menu['children'][$key]['menu_url'] = '#';
        //                 $menu['children'][$key]['spa_route'] = false;
        //             }
        //             if ($children['badge']) {
        //                 $menu['children'][$key]['badge_count'] = getBadge($children['badge']);
        //             }
        //         }
        //     }
        //     return $menu;
        // });
    }

    public function getNotificationCountAttribute()
    {
        // $notif = 0;
        // if (auth()->check()) {
        //     $user_id = auth()->user()->id;
        //     $notif = Notification::where('status', 0)->where(function ($query) use ($user_id) {
        //         $query->where('user_id', $user_id);
        //     })->count();
        // }

        // return $notif;
    }

    public function getNotificationAttribute()
    {
        // $notif = 0;
        // if (auth()->check()) {
        //     $user_id = auth()->user()->id;

        //     $notif = Notification::where('user_id', $user_id)->limit(3)->orderByDesc('created_at')->get();
        //     return $notif;
        // }
    }

    // has many cart
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Get the brand that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get all of the addressUsers for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addressUsers()
    {
        return $this->hasMany(AddressUser::class);
    }

    /**
     * Get all of the downlines for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function downlines()
    {
        return $this->hasMany(Downline::class, 'user_id');
    }

    /**
     * Get all of the downlines for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function memberDownlines()
    {
        return $this->hasMany(Downline::class, 'member_id');
    }

    /**
     * Get the brand that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->hasOne(Company::class, 'user_id');
    }

    public function userdata()
    {
        return $this->belongsTo(UserData::class);
    }

    /**
     * The userWarehouse that belong to the Warehouse
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_users');
    }

    /**
     * Get the agentDetail associated with the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function agentDetail()
    {
        return $this->hasOne(AgentDetail::class, 'user_id');
    }


    /**
     * The domains that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function domains()
    {
        return $this->belongsToMany(Domain::class, 'agent_domain', 'user_id', 'domain_id');
    }

    /**
     * The brands that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_user');
    }

    public function getBrandIdsAttribute()
    {
        return $this->brands()->pluck('brands.id')->toArray();
    }

    /**
     * The brands that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'name']);
    }

    public function getStatusAgentAttribute()
    {
        return $this->domains()->where('user_id', $this->id)->exists();
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by);
        return $user ? $user->name : '-';
    }

    public function getServiceGineeUrlAttribute()
    {
        return getSetting('SERVICE_GINEE_URL');
    }

    public function getDepositAttribute()
    {
        return $this->hasMany(OrderDeposit::class, 'contact')->sum('amount');
    }

    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'user_has_voucher', 'user_id', 'voucher_id');
    }

    /**
     * The contactDownlines that belong to the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contactDownlines()
    {
        return $this->hasMany(ContactDownline::class, 'user_id');
    }

    public function getCompanyNameAttribute()
    {
        $company = Company::where('user_id', $this->id)->first(['name']);
        if ($company) {
            return $company->name;
        }

        return '';
    }

    public function getAccountIdAttribute()
    {
        $account = CompanyAccount::where('status', 1)->first();

        if ($account) {
            return $account->id;
        }

        return 1;
    }

    public function getSalesChannelsAttribute()
    {
        return explode(',', $this->sales_channel);
    }

    public function getAmountDetailAttribute()
    {
        $total_order_lead = 0;
        $total_order_manual = 0;
        $total_invoice = 0;
        $total_amount = 0;
        $debt_order_leads = OrderLead::whereContact($this->id)->where('status', 2)->get();
        foreach ($debt_order_leads as $key => $value) {
            $debt = $value->amount - $value->amount_billing_approved;
            $total_invoice += 1;
            $total_amount += $value->amount_billing_approved;
            $total_order_lead += $debt > 0 ? $debt : 0;
        }

        $debt_order_manuals = OrderManual::whereContact($this->id)->where('status', 2)->get();
        foreach ($debt_order_manuals as $key => $value) {
            $debt = $value->amount - $value->amount_billing_approved;
            $total_invoice += 1;
            $total_amount += $value->amount_billing_approved;
            $total_order_manual += $debt > 0 ? $debt : 0;
        }
        $total_debt = $total_order_lead + $total_order_manual;

        return [
            'total_order_lead' => $total_order_lead,
            'total_order_manual' => $total_order_manual,
            'total_invoice' => $total_invoice,
            'total_amount' => $total_amount,
            'total_debt' => $total_debt,
        ];
    }

    public function getAmountCanWithdrawAttribute()
    {
        if ($this->role) {
            $commision = Transaction::whereHas('userCreated', function ($query) {
                return $query->whereHas('roles', function ($query) {
                    $query->where('role_type', $this->role->role_type);
                });
            });

            return $commision->where('status_delivery', 4)->where('status', 7)->sum('commission');
        }

        return 0;
    }

    public function getCompanyIdAttribute()
    {
        if (auth()->check()) {
            $company = CompanyUser::where('user_id', auth()->user()->id)->first(['company_id']);
            $account_id = $company ? $company->company_id : 1;

            return $account_id;
        }
        return 1;
    }

    public function getAppendixAttribute()
    {
        if ($this->company) {
            // if ($this->company->npwp_name == 'PT. KIMIA FARMA APOTEK') {
            if ($this->amount_detail['total_debt'] > 10000000) {
                return '03';
            }
            // }

            if ($this->company->npwp_name == 'PT Bintang Makmur Sinar Abadi') {
                return '07';
            }
        }
        return '01';
    }

    public function getTeleponAttribute($value)
    {
        return formatPhone($value, '+628');
    }
}
