# Loan API
This is an application develoed as part of interview assignment.

here api allows authenticated user to create loan request. initially loan will be in `pending` state.

once user has raised a request for the loan, he/she can see thair own loan along with complete status about how much its completed and all.

Admin user will approve the loan, as admin approved the loan, it will change status to `approoved`

once the loan get approved from the admin, user can pay the installments.

This is all about the basic understanding of the developed code.

## Installation and running

* clone the reppo
* make sure your database service is up and running
* create database with any name i.e. `loan_api`
* copy `.env.example` file to `.env` file.
* make sure you have setup correct env variables in the `.env` file. (specially the database related variables must be correct)
* run the migrations `php artisan migrate`
* run application `php artisan serve` this will start your server on port `8000` by default.

## API

**Authentication**

Request - `POST /api/register`  
Data (raw json) -
```json
{
    "name": "Harikrushna",
    "email": "adiechahari@gmail.com",
    "password": "123456",
    "password_confirmation": "123456"
}
```
This will create new user in the database and also provide login token, so you don't need to call login api after this api call.

---
Request - `POST /api/login`  
Data (raw json) -
```json
{
    "email": "adiechahari@gmail.com",
    "password": "123456"
}
```
This api login with existing database user and provide authencation token. to make calls to the protected urls.

---
Request - `POST /api/logout` (no data)  
Policy - authenticated user  
this will remove all the tokens that are issued for the logged in user.

---
Request - `GET /api/user` (no data)  
Policy - authenticated user  
This will return authenticated user.

**Loan**
Request - `GET /api/loans` (no data)  
Policy - authenticated user  
This will return all the loan instances that are belongs to the authenticated user.

---
Request - `POST /api/loans`  
Data (raw json) -
```json
{
    "amount": 10000,
    "term": 3
}
```
Policy - authenticated user  
This will create loan request according to the given data. and return the detailed loan object with all the repaymets.

---
Request - `GET /api/loans/{loanId}`  
Policy - authenticated user  
This will return the detailed loan object with all the repaymets which belongs to the given loanId.

---
Request - `POST /api/loans/{loanId}/pay`  
Data (raw json) -
```json
{
    "amount": 5000
}
```
Policy - authenticated user  
This will create payment for given amount agains the loan and return detailed loan object with all the repaymets with updated their payment status.

here the there is some constrain as mention in the documentation you can send amount that should be greator than of expected amount, and the expected amount will be calculated by minimum of installment amount or pending amount

installment amount will be total loan amount divided by the terms

**Admin**

Before talking about the admin api, just see how to make any normal user to become an admin user. 

_Become an Admin user_

- Make sure the user is already registered.
- just run command `php artisan become:admin <email>`

the command will make the normal user to an admin user.

