<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->registerPolicies();
        // Passport::routes();
        // Passport::tokensExpireIn(now()->addDays(15));

        // Passport::refreshTokensExpireIn(now()->addDays(15));
        $this->registerPolicies();

        Passport::routes();
    
        // Passport::tokensExpireIn(now()->addDays(15));
        // Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addDays(15));
        //
    }
}
// eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIzIiwianRpIjoiNDY4YjAyMjM3ZmUwYzY1ZjE3MTQwNzk2YjAzYzg2NDRlZjFjYWJhMjZkMTA2YTJmMWU1MmUxNjM4ZmE2ZjQyOTNiMjQ3MThjMTM3ZTVlZTQiLCJpYXQiOjE2Mzk3MjI1NTAsIm5iZiI6MTYzOTcyMjU1MCwiZXhwIjoxNjcxMjU4NTUwLCJzdWIiOiIxMCIsInNjb3BlcyI6W119.OOVvzdQ6fJFGC_B2XhYIfBrWD-OVCHIw6kmnKwT2O4iFCKscFEb13OKGpQWZKE0qb1OW0VrZy82EhjKQo1qGIjwnmzlXKCPzy-EF_XoNuNQrg2r8HI0_VuP-loTYD0z4yRFPtnSc9Cv8hdLIH0ChvUPo725UTeozFOy5bMTbTkUxfVGIrd8mkDJAv6AI0BGgy-O3J8KNiUn8FW_KPyYLz4WxmlumHmGPRC-tqe_m-vj3mvF-ov0Kv1DFjxIx8WDS4iaOqFt5jYzOKT1obZLF-S-VUrCqv2auCqjuipWN34TuOgMcB_a0i6WjIHev9z7UL4jC-hCNzAzIWvy8TlgauoLewQdgrDCYDUbt_dmYPzcnhRYTTPTbs5A2Kal-FmsTUkhYgylQDZepHNZ4jMPnzp_H9R_mBZsmlcOUYnp-95_Hrr73ziigunxLJ9yvmsr5kobnV6V-8PUTW2p7j95RKHz5YgH6_59H-Ge_4fgONbqk5PoNDRKUNHklJ8inJshB5aWF3HosVAAn7y_r6ixSDs7J2SSk6mhm_aesaf0EuJAPeklyBeOWIuGLthO-AVMZm096nlODNNu9Ozo0SWvW34v23qZeDKzUsupR9wXdC7swc0HKVYtSpHZ-lLVESwJN_RBPVNopnTv3Dkp_mduXOOX4yM5sZnGD7LVaU2IWWF0



// 8f24719bd010014848352ccf975606284f60eb8d87c4fe2cb483b3059bc50876aa5e04c1a337461a