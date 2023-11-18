# Document verification API

This is a simple API that implements a json document verification.


## Testing server building

For building an image at the first time:

```bash
make build
```

This will build and up the containers for API and also for testing frontend.


### To stop the server

```bash
make stop
```

### To start server again

```bash
make start
```

## Testing with phpUnit

For running tests run (within a running server):

```bash
make test
```

## Testing with frontend

After the containers are run, you can test file uploading and verification with a simple frontend built using React. 

The frontend is located at the URL  http://localhost:4173.

First, you need to sign up to create an account. After account creation, you can sign in and use the file uploading form.

**! Note:** the frontend's file upload form accepts `.oa` files and also `.pdf` files for testing purposes. Please upload a .pdf file to test for an **invalid file format** error.

For testing different server responses, please use the files from the `/tests/test_files/` directory.

