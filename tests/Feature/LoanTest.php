<?php

namespace Tests\Feature;

use App\Casts\LoanStatus;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user_token = $this->user->createToken('auth_token')->plainTextToken;
        $this->admin = User::factory()->create(["roles" => ["admin"]]);
        $this->admin_token = $this->admin->createToken('auth_token')->plainTextToken;
    }

    public function headers($name) {
        $token = $name . "_token";
        return [
            "Authorization" => "Bearer " . $this->$token,
            "Content-Type" => "application/json",
        ];
    }

    public function test_user_can_get_loans() {
        $res = $this->withHeaders($this->headers('user'))->get('/api/loans');
        $res->assertStatus(200);
        $res->assertJsonPath("data", []);
        $res->assertJsonPath("total", 0);
    }

    public function test_user_can_create_loan_request() {
        $data = ["amount" => 3000, "term" => 2];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertCreated();
        $this->assertDatabaseCount('loans', 1);
    }

    public function test_loan_amount_validation() {
        // Required validation check
        $data = ["term" => 2];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "The given data was invalid.");
        $res->assertJsonPath('errors.amount', ["The amount field is required."]);

        // Type validation check
        $data = ["amount" => "something", "term" => 2];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "The given data was invalid.");
        $res->assertJsonPath('errors.amount', ["The amount must be a number."]);

    }

    public function test_loan_term_validation() {

        // Required validation
        $data = ["amount" => 20000];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "The given data was invalid.");
        $res->assertJsonPath('errors.term', ["The term field is required."]);

        // Type validation
        $data = ["amount" => 20000, "term" => "string"];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "The given data was invalid.");
        $res->assertJsonPath('errors.term', ["The term must be an integer."]);

        $data = ["amount" => 20000, "term" => 333.2];
        $res = $this->postJson('/api/loans', $data, $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "The given data was invalid.");
        $res->assertJsonPath('errors.term', ["The term must be an integer."]);

    }

    public function test_loan_approval_by_admin() {
        // Creating a loan
        $loan = Loan::factory()->create();

        // verify loan status
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::PENDING);

        // Approve by admin
        $approve_url = '/api/loans/' . $loan->id . '/approve';
        $admin_res = $this->withHeaders($this->headers('admin'))->post($approve_url);
        $admin_res->assertOk();

        // verify database
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::APPROVED);
    }

    public function test_loan_should_not_approval_by_user() {
        // Creating a loan
        $loan = Loan::factory()->create();

        // verify loan status
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::PENDING);

        // Approve by normal user
        $approve_url = '/api/loans/' . $loan->id . '/approve';
        $admin_res = $this->withHeaders($this->headers('user'))->post($approve_url);
        $admin_res->assertStatus(403);

        // verify database
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::PENDING);
    }

    public function test_user_can_not_pay_pending_loan_installment() {
        // Create approved loan instance
        $loan = Loan::factory()->create([
            "amount" => 10000,
            "terms" => 3,
            "user_id" => $this->user->id
        ]);

        // Verify loan is in pending status
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::PENDING);

        // Making payment
        $url = '/api/loans/' . $loan->id . '/pay';
        $res = $this->postJson($url, [
            "amount" => 3333.33
        ], $this->headers('user'));
        $res->assertStatus(422);
        $res->assertJsonPath('message', "Loan is in 'pending' state. unable to process the payment.");

    }

    public function test_user_can_pay_approved_loan_installment() {
        // Create approved loan instance
        $loan = Loan::factory()->create([
            "amount" => 10000,
            "terms" => 3,
            "user_id" => $this->user->id,
            "status" => LoanStatus::APPROVED
        ]);

        // Verify loan is in pending status
        $loan->refresh();
        $this->assertTrue($loan->status === LoanStatus::APPROVED);

        // Making payment
        $url = '/api/loans/' . $loan->id . '/pay';
        $res = $this->postJson($url, [
            "amount" => 3333.33
        ], $this->headers('user'));
        $res->assertStatus(201);
        $res->assertJson([
            "id" => 1,
            "started_on" => now()->format("d-m-Y"),
            "amount" => 10000,
            "terms" => 3,
            "status" => "approved",
            "payment_recieved" => 3333.33,
            "repayments" => [[
                "due_date" => str(now()->addDay(7),)
                "amount" => 3333.33,
                "status" => "pending"
            ], [
                "due_date" => str(now()->addDay(14)),
                "amount" => 3333.33,
                "status" => "pending"
            ], [
                "due_date" => str(now()->addDay(21)),
                "amount" => 3333.34,
                "status" => "pending"
            ]]
        ]);

    }



}
