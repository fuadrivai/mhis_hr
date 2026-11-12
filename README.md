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

### Mobile Visitor API

All endpoints require the existing `Authorization: bearer <token>` session header and an active employee profile. Send `Accept: application/json`. The server identifies the creator or host from the session; clients must not send a user or employee ID to select whose visits to access. The host list contains visits assigned to the logged-in employee; monitoring contains visits registered by the logged-in user. Either the host or creator can view details, but only the creator can cancel. An inaccessible or nonexistent visit returns 404.

**Form options:** `GET /api/visitor/form-options` returns:

```json
{
  "data": {
    "branches": [{"id": 1, "name": "School"}],
    "categories": [{"id": 1, "name": "Guest", "requires_approval": false, "requires_additional_info": false}],
    "purposes": [{"id": 1, "name": "Meeting", "requires_approval": false}],
    "default_branch_id": 1,
    "default_host_employee_id": 10
  }
}
```

Non-admin users can register visits only in their assigned branch. `GET /api/visitor/hosts?search=Name&per_page=20&page=1` returns a Laravel paginated response whose `data` contains host employee `id` and `name`. Both search and pagination parameters are optional; `per_page` is 1–100.

**Create:** `POST /api/visitor/invitations` accepts JSON or multipart form data. Dates in the example are illustrative:

```json
{
  "full_name": "Guest Name",
  "phone": "08123456789",
  "email": null,
  "company": "Example",
  "visitor_category_id": 1,
  "visitor_purpose_id": 1,
  "branch_id": 1,
  "host_employee_id": 10,
  "visit_date": "2027-01-20",
  "expected_arrival_date": "20 January 2027",
  "expected_arrival_time": "09:00",
  "expected_departure_date": "20 January 2027",
  "expected_departure_time": "10:00",
  "notes": "Meeting",
  "visitor_notes": null
}
```

Provide at least one of `phone` or `email`. `company`, `notes`, and `visitor_notes` are optional; some categories require either notes field. An optional `photo` must be uploaded as multipart (`jpg`, `jpeg`, `png`, or `webp`, max 5 MB). Arrival and departure must be on `visit_date`, and departure must be later. The response is HTTP 201 (`expected`) or HTTP 202 (`pending_confirmation`):

```json
{
  "message": "Visitor invitation created.",
  "data": {
    "id": 42,
    "visit_code": "ABCDE",
    "status": "expected",
    "visit_date": "2027-01-20",
    "expected_arrival_at": "2027-01-20T09:00:00+07:00",
    "expected_departure_at": "2027-01-20T10:00:00+07:00",
    "branch_id": 1,
    "host_employee_id": 10
  }
}
```

Keep `data.id` for the detail and cancellation endpoints. Approval remains available through the existing web flow; this API does not expose an approval endpoint or a QR token.

**Lists:** `GET /api/visitor/invitations` lists visits for the authenticated host. `GET /api/visitor/monitoring` lists visits created by the authenticated user, including invitations for other hosts. A self-created invitation appears in both lists. Both return a Laravel paginated object, with visits in `data` and pagination fields such as `current_page`, `last_page`, `per_page`, and `total`. Query parameters are optional:

- `view=all|today|upcoming|history` (default `all`): today uses the application's timezone; upcoming contains future `expected` or `pending_confirmation` visits; history contains `completed`, `rejected`, `cancelled`, and `no_show`.
- `status=pending_confirmation|expected|rejected|checked_in|completed|cancelled|no_show`.
- `from=YYYY-MM-DD`, `to=YYYY-MM-DD` (inclusive visit dates).
- `search` searches visitor name, phone, email, or an exact visit code.
- `per_page` (1–100; default 20) and `page`.

Filters may be combined. Results are ordered by visit date descending, then ID descending.

**Detail:** `GET /api/visitor/invitations/{id}` is accessible to the host and creator and returns `{"data": ...}`. Each list item and detail uses the same shape. `can_cancel` is false for a host who is not also the creator:

```json
{
  "data": {
    "id": 42,
    "visit_code": "ABCDE",
    "registration_method": "employee_invitation",
    "status": "expected",
    "visit_date": "2027-01-20",
    "expected_arrival_at": "2027-01-20T09:00:00+07:00",
    "expected_departure_at": "2027-01-20T10:00:00+07:00",
    "check_in_at": null,
    "check_out_at": null,
    "notes": "Meeting",
    "visitor_notes": null,
    "cancellation_reason": null,
    "can_cancel": true,
    "visitor": {"id": 7, "full_name": "Guest Name", "phone": "08123456789", "email": null, "company": "Example"},
    "host": {"employee_id": 10, "name": "Host Name"},
    "branch": {"id": 1, "name": "School"},
    "category": {"id": 1, "name": "Guest"},
    "purpose": {"id": 1, "name": "Meeting"}
  }
}
```

**Cancel:** `POST /api/visitor/invitations/{id}/cancel` with `{"reason":"Schedule changed"}` returns `{"message":"Visit cancelled.","data":{...}}` with the same detail shape and `status=cancelled`. The reason is required and limited to 2,000 characters. Only the creator can cancel a visit that is `expected` or `pending_confirmation` and whose visit date has not passed. The service rechecks this rule when processing the request; `can_cancel` is only a UI hint.

Missing or invalid session credentials, inactive employees, and unauthorized branches return HTTP 403. Validation failures and visits that can no longer be cancelled return HTTP 422 with Laravel's `message` and `errors` fields.

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
