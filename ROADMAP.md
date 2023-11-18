# Document verification API

This is a simple API that implements a json document verification.

# Implemented files

The controller files of this API are located in the `app/Http/Controllers/Api` directory:

- `AuthController.php` provides a user authentication methods;
- `VerificationController.php` provides all the methods for a document verification.

Routes are described in a `routes/api.php` file. This API contains the routes listed below:

- `/signup`
- `/signin`
- `/signout`
- `/user`
- `/verify`
- `/results`

PhpUnit tests described in a `tests/Feature/VerificationControllerTest.php` file. 

There are a directory `/tests/test_files` that contains a files with different invalid formats for testing purposes.
Please use a .pdf file in this directory to test for an **invalid file format** and **invalid file size** errors.


