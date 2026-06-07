<?php

class LoanRepayment
{
    // DB Stuff
    private $con;
    private $loans_table           = "vehicle_loans";
    private $loan_repayments_table = "loan_repayments";

    // Loan Properties
    public $loan_id;
    public $vehicle_id;
    public $principal;
    public $interest_rate;
    public $start_date;
    public $end_date;
    public $repayment_method;

    // Loan Repayment Properties
    public $repayment_id;
    public $amount;
    public $source;
    public $booking_id;
    public $paid_at;

    // Constructor with DB
    public function __construct($db)
    {
        $this->con = $db;
    }

    // ---------------------------------------- LOAN FUNCTIONS ----------------------------------------

    public function createLoan()
    {
        $query = "INSERT INTO {$this->loans_table}
                  (vehicle_id, principal, interest_rate, start_date, end_date, repayment_method)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            $this->vehicle_id, $this->principal, $this->interest_rate,
            $this->start_date, $this->end_date, $this->repayment_method,
        ]);
    }

    public function getAllLoans()
    {
        $query = "SELECT * FROM {$this->loans_table} WHERE deleted = 0 ORDER BY created_at DESC";
        $stmt  = $this->con->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLoanById($id)
    {
        $query = "SELECT * FROM {$this->loans_table} WHERE id = ? AND deleted = 0";
        $stmt  = $this->con->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLoan($id)
    {
        $query = "UPDATE {$this->loans_table}
                  SET principal=?, interest_rate=?, start_date=?, end_date=?, repayment_method=?
                  WHERE id=? AND deleted=0";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            $this->principal, $this->interest_rate, $this->start_date,
            $this->end_date, $this->repayment_method, $id,
        ]);
    }

    public function deleteLoan($id)
    {
        $query = "UPDATE {$this->loans_table} SET deleted=1 WHERE id=?";
        $stmt  = $this->con->prepare($query);
        return $stmt->execute([$id]);
    }

    // ---------------------------------------- LOAN REPAYMENT FUNCTIONS ----------------------------------------

    public function createRepayment()
    {
        $query = "INSERT INTO {$this->loan_repayments_table}
                  (loan_id, amount, source, booking_id, paid_at)
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            $this->loan_id, $this->amount, $this->source,
            $this->booking_id, $this->paid_at,
        ]);
    }

    public function getRepaymentsByLoan($loan_id)
    {
        $query = "SELECT * FROM {$this->loan_repayments_table} WHERE loan_id = ? AND deleted=0 ORDER BY paid_at ASC";
        $stmt  = $this->con->prepare($query);
        $stmt->execute([$loan_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRepayment($id)
    {
        $query = "UPDATE {$this->loan_repayments_table}
                  SET amount=?, source=?, booking_id=?, paid_at=?
                  WHERE id=? AND deleted=0";
        $stmt = $this->con->prepare($query);
        return $stmt->execute([
            $this->amount, $this->source, $this->booking_id, $this->paid_at, $id,
        ]);
    }

    public function deleteRepayment($id)
    {
        $query = "UPDATE {$this->loan_repayments_table} SET deleted=1 WHERE id=?";
        $stmt  = $this->con->prepare($query);
        return $stmt->execute([$id]);
    }
}
