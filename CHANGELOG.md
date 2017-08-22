CRUDlexUser Changelog
=====================

## 0.13.0
Released: Upcoming
- Adjusted to CRUDlex 0.12.0 APIs
- Updated dependencies:
	- "symfony/security": "~3.3"
	- "symfony/browser-kit": "~3.3"
	- "symfony/css-selector": "~3.3"
    - "eloquent/phony": "~1.0"

## 0.12.0
Released: 2017-03-26
- Relaxed a bit the required CRUDlex version
- Updated dependencies:
	- "symfony/security": "~3.2"
	- "symfony/browser-kit": "~3.2"
	- "symfony/css-selector": "~3.2"

## 0.11.0
Released: 2016-09-26
- Supporting a many-to-many relationship between user and roles

## 0.10.0
Released: 2016-09-18
- Updated to the CRUDlex 0.10.0 API
- Attention: With Silex 2, the default password encoder changed to BCrypt. Added a constructor to override it if something else is used like the previous default MessageDigestPasswordEncoder
- Attention: The minimum PHP version is now 5.5
- Attention: Switched from PSR-0 to PSR-4
- Switched to the array shorthand
- Updated dependencies:
	- "symfony/security": "~3.1"
	- "symfony/browser-kit": "~3.1"
	- "symfony/css-selector": "~3.1"

## 0.9.10
Released: 2016-07-19
- Updated to the CRUDlex 0.9.10 API
- Fixed some issues found by static code analysis
- Changed the User class so you can actually get the underlying entity and so all your fields
- Changed the setup recommendation to underscore template names

## 0.9.9
Released: 2016-03-01
- Updated to CRUDlex 0.9.9
- Updated depedencies

## 0.9.8
Released: 2016-03-01

First release.
