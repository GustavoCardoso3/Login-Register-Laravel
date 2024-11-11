# Laravel Login & Register System with OAuth and Email Verification

This project is a simple Laravel-based authentication system that provides login, registration, email verification, passowrd reset, and integration with Google and GitHub OAuth services. It allows users to register and log in using traditional email/password authentication or via their Google or GitHub accounts. Email verification is also integrated into the process to ensure user identity.

## Features

- **User Registration**: Users can register via email and password with basic validation.
- **User Login**: Users can log in with email and password or using their Google/GitHub account.
- **Email Verification**: After registration, users must verify their email address by clicking on a verification link sent to their inbox.
- **Password Reset**: Users can reset their passwords.
- **OAuth Authentication**: Users can log in using their Google or GitHub accounts via OAuth 2.0.
- **Session Management**: Once logged in, users can stay logged in or log out when desired.
- **Protected Routes**: Some routes are protected and require the user to be authenticated and have a verified email.

## Installation

Follow these steps to set up the project locally:

### Prerequisites

- [PHP](https://www.php.net/) (>= 7.3)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/)
- [Laravel](https://laravel.com/)

### Steps to Install

1. Clone the repository.
2. Install all dependecies.
3. Migrate the data base.
4. Create a env file based on .env.example.
5. Set up mail configuration in .env.
6. Add all the credentials for gitHub and google.


## Contributing

If you'd like to contribute to this project, please fork the repository and create a pull request with your changes. Ensure that any new features or bug fixes are accompanied by tests.

## License

This project is open-source and available under the MIT License.

## Acknowledgements

- [Laravel] - The PHP framework used for this project.
- [Socialite] - Laravel package for OAuth authentication.
- [Google Developer] - For obtaining OAuth credentials for Google login.
- [GitHub Developer] - For obtaining OAuth credentials for GitHub login.
