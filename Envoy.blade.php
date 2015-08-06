@servers(['production' => '-p 8022 root@162.213.254.96')

@task('deploy', ['on' => 'production'])
cd /var/www/savemyga.me
git pull origin master
php artisan migrate
@endtask