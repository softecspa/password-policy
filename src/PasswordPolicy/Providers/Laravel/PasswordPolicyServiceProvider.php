<?php namespace PasswordPolicy\Providers\Laravel;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use PasswordPolicy\PolicyBuilder;
use PasswordPolicy\PolicyManager;
use PasswordPolicy\Providers\Laravel\Facade;

/**
 * Class PasswordPolicyServiceProvider
 *
 * @package PasswordPolicy\Providers\Laravel
 */
class PasswordPolicyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $configPath = __DIR__ . '/../../../../config/password-policy.php';
        $this->mergeConfigFrom($configPath, 'password-policy');

        $this->registerManager();
        $this->registerBuilder();
        $this->registerFacade();
        $this->defineDefaultPolicy();
    }

    /**
     * Boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../../../config/password-policy.php';
        $this->publishes([$configPath => $this->getConfigPath()], 'config');

        $this->configureValidationRule();
    }

    /**
     * Register the policy manager within the Laravel container
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->app->singleton(PolicyManager::class);
    }

    /**
     * Register policy builder
     *
     * @return void
     */
    protected function registerBuilder()
    {
        $this->app->bind(PolicyBuilder::class);
    }

    /**
     * Configure custom Laravel validation rule
     *
     * @return void
     */
    protected function configureValidationRule()
    {
        $this->app['validator']->extend($this->app['config']->get('password-policy.rule'), PasswordValidator::class . '@validate');
    }

    /**
     * Register password policy facade
     *
     * @return void
     */
    protected function registerFacade()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('PasswordPolicy', Facade::class);
    }

    /**
     * Define the default password policy
     *
     * @return void
     */
    protected function defineDefaultPolicy()
    {
        $defaultPolicy = $this->defaultPolicy($this->app->make(PolicyBuilder::class));

        if ($defaultPolicy instanceof PolicyBuilder) {
            $defaultPolicy = $defaultPolicy->getPolicy();
        }

        $this->app->make(PolicyManager::class)->define('default', $defaultPolicy);
    }

    /**
     * Build the default policy instance
     *
     * @param PolicyBuilder $builder
     *
     * @return \PasswordPolicy\Policy
     */
    protected function defaultPolicy(PolicyBuilder $builder)
    {
        return $builder->getPolicy();
    }

    /**
     * Get the config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return config_path('password-policy.php');
    }
}
