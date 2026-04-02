- `docker compose -f compose.test.prod.yml up --build -d`
- `docker compose -f compose.test.prod.yml exec app sh`
    - `php artisan db:seed`

Show redis container log:
- `docker compose exec redis sh -lc 'redis-cli -a "$REDIS_PASSWORD" MONITOR'`
```
openssl req -x509 -newkey rsa:2048 -nodes -days 365 \
  -keyout /etc/laravel/ssl/server.key -out /etc/laravel/ssl/server.crt \
  -subj "/CN=mpi.stijnrombouts.be"
```


Show users with their roles in the database:
```
docker exec app php artisan tinker --execute="App\Models\User::with('roles:role_id,role')->get(['user_id', 'username', 'email', 'first_name', 'last_name'])->each(function(\$user) { echo 'ID: ' . \$user->user_id . ' | Username: ' . \$user->username . ' | Email: ' . \$user->email . ' | Name: ' . \$user->first_name . ' ' . \$user->last_name . ' | Roles: ' . \$user->roles->pluck('role')->implode(', ') . PHP_EOL; });"
ID: 1 | Username: rafhensbergen | Email: superadmin@example.com | Name: Raf Hensbergen | Roles: SuperAdmin
ID: 2 | Username: Rafh | Email: raf.hensbergen@mpi-oosterlo.be | Name: Raf  Hensbergen | Roles: Admin
ID: 3 | Username: madwhite | Email:  | Name: Madeleine White | Roles: Researcher
```
