<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## MHIS HUB Visitor Management

The Visitor Management module provides employee invitations, host approvals, walk-in registration, a unified Security Desk for check-in/check-out, visit history, reports, and configurable visitor categories and purposes.

Run the application migrations to create the visitor tables. Run `php artisan db:seed --class=RoleSeeder` to add the `security`, `ga`, and `visitor-manager` roles, then assign those roles through the existing user-role management screens. Security and branch-level roles require the user's employee record to have an employment branch. Admin users can access all branches.

Invitation and arrival notifications use the configured Laravel mail transport. Each visit uses a random opaque QR token, and lookups use only its hash. A pending approval temporarily retains an encrypted token copy so the visitor can receive the QR code after approval; that copy is removed after processing. The token is also shown once when the invitation is created.

The employee invitation API is `POST /api/visitor/invitations` and uses the existing `auth_login` middleware. It accepts the same validated invitation fields as the web form and calls the shared `VisitorManagementService`; successful invitations return HTTP 201, while invitations awaiting host approval return HTTP 202. Guest email delivery and inline QR generation are handled by the service flow for both web and API requests.

## Time-off next approval API

`GET /api/time/request/approval/{approvalId}/next` uses the existing `Authorization` session token. Pass the current approval's top-level `id` from `GET /api/time/request/approval`, not `approval_request.id`. No request body or user ID is required.

The endpoint finds the next pending approval assigned to the authenticated user whose earlier steps are no longer pending and whose request is still pending. It uses the same descending time-off date and approval ID order as the approval list, wraps to the beginning, and ignores the list's month, year, status, and other filters. It can be called after the current approval is processed; it will not return another step of the same request.

The response is `{"data": <approval>}`, with the same approval and related request fields as an item in the approval list. If no other eligible request exists, it returns `{"data": null}` with HTTP 200. An approval that is not visible to the current user or does not exist returns HTTP 404; a missing or invalid session token returns HTTP 403.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
