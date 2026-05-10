<?php
/**
 *
 */
class Account
{
    // DB STUFF
    private $con;
    private $table = "accounts";

    // Account properties
    public $id;
    public $name;
    public $email;
    public $password;        // password that comes from the user
    public $hashed_password; // password that comes from the db
    public $phone_no;
    public $country;
    public $role_id;
    public $role_ids = []; // array of multiple roles from account_roles
    public $category_id;   //category_id that is used to get agent rates
    public $agent_rate;
    public $deleted;

    public function __construct($db)
    {
        /// when we instantiate a new post we'll pass in the db as a parameter
        $this->con = $db;
    }

    // create an agent account
    public function create_agent_with_roles($data, $roleIds)
    {
        try {
            $this->con->beginTransaction();

            // Default password for new agents
            $defaultPassword = '1234';
            $hashedPassword  = password_hash($defaultPassword, PASSWORD_BCRYPT);

            // Legacy role_id: use first role for compatibility
            $legacyRoleId = ! empty($roleIds) ? $roleIds[0] : null;

            // Insert into accounts
            $sql = "INSERT INTO accounts
                (name, email, password, phone_no, country, role_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->con->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['phone_number'],
                $data['country'],
                $legacyRoleId,
            ]);

            $accountId = $this->con->lastInsertId();

            // Assign roles in account_roles
            if (! empty($roleIds)) {
                $stmtRole = $this->con->prepare("INSERT INTO account_roles (account_id, role_id) VALUES (?, ?)");
                foreach ($roleIds as $roleId) {
                    $stmtRole->execute([$accountId, $roleId]);
                }
            }

            $this->con->commit();

            return [
                "status"           => "Success",
                "message"          => "Agent created successfully",
                "account_id"       => $accountId,
                "default_password" => $defaultPassword, // for admin visibility
                "roles"            => $this->fetch_all_account_roles($accountId),
            ];
        } catch (Exception $e) {
            $this->con->rollBack();
            return ["status" => "Error", "message" => $e->getMessage()];
        }
    }
    // read all accounts except super user
    public function read_all()
    {
        $sql = "SELECT a.id, a.name, a.email, a.phone_no, a.country, r.name AS role
            FROM accounts a
            INNER JOIN roles r ON a.role_id = r.id
            WHERE a.role_id != 0
            ORDER BY a.id DESC";

        $stmt = $this->con->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    // Get an agent's details
    public function read_agent_details()
    {
        $sql = "SELECT a.id, a.name, a.email, a.phone_no, a.country
            FROM accounts a
            WHERE a.id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $row) {
            return false;
        }

        // Fetch roles separately (supports multiple roles)
        $sqlRoles = "SELECT r.id, r.name
                 FROM account_roles ar
                 JOIN roles r ON ar.role_id = r.id
                 WHERE ar.account_id = ?";
        $stmtRoles = $this->con->prepare($sqlRoles);
        $stmtRoles->execute([$this->id]);
        $roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

        // Legacy role_id support (super accounts)
        if ($row['id']) {
            $legacySql  = "SELECT role_id FROM accounts WHERE id = ?";
            $stmtLegacy = $this->con->prepare($legacySql);
            $stmtLegacy->execute([$this->id]);
            $legacyRoleId = $stmtLegacy->fetchColumn();

            if ($legacyRoleId === "0") {
                $roles[] = ["id" => 0, "name" => "super account"];
            }
        }

        return [
            "id"       => $row['id'],
            "name"     => $row['name'],
            "email"    => $row['email'],
            "phone_no" => $row['phone_no'],
            "country"  => $row['country'],
            "roles"    => $roles,
        ];
    }

    // update agent details with roles
    public function update_agent_with_roles($roleIds)
    {
        try {
            // Begin transaction
            $this->con->beginTransaction();

            // Update account details
            $sql = "UPDATE accounts
                SET name = ?, email = ?, phone_no = ?, country = ?
                WHERE id = ?";
            $stmt = $this->con->prepare($sql);
            $stmt->execute([
                $this->name,
                $this->email,
                $this->phone_no,
                $this->country,
                $this->id,
            ]);

            // Clear existing roles
            $sqlDelete  = "DELETE FROM account_roles WHERE account_id = ?";
            $stmtDelete = $this->con->prepare($sqlDelete);
            $stmtDelete->execute([$this->id]);

            // Insert new roles
            if (! empty($roleIds)) {
                $sqlInsert  = "INSERT INTO account_roles (account_id, role_id) VALUES (?, ?)";
                $stmtInsert = $this->con->prepare($sqlInsert);
                foreach ($roleIds as $roleId) {
                    $stmtInsert->execute([$this->id, $roleId]);
                }
            }

            // Commit transaction
            $this->con->commit();

            return [
                "status"     => "Success",
                "message"    => "Agent updated successfully",
                "account_id" => $this->id,
            ];
        } catch (Exception $e) {
            $this->con->rollBack();
            return [
                "status"  => "Error",
                "message" => "Update failed: " . $e->getMessage(),
            ];
        }
    }

    // Update an agent / account's password
    public function update_password()
    {
        $sql  = "UPDATE accounts SET password = ? WHERE id = ?";
        $stmt = $this->con->prepare($sql);
        if ($stmt->execute([$this->hashed_password, $this->id])) {
            return true;
        } else {
            // print error if something goes wrong
            printf("Error :  % s . \n ", $stmt->error);
            return false;
        }

    }

    // this function fetches the account for login purposes. It ensures that the account exists with the given email
    public function fetch_account_with_roles()
    {
        $sql = "SELECT a.id, a.name, a.email, a.password, r.id AS role_id, r.name AS role_name
            FROM accounts a
            LEFT JOIN account_roles ar ON a.id = ar.account_id
            LEFT JOIN roles r ON ar.role_id = r.id
            WHERE a.email = ?
            LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->email]);
        return $stmt;
    }

    public function fetch_agent_rate()
    {
        $sql  = "SELECT rate FROM agent_rates WHERE agent_id = ? AND category_id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id, $this->category_id]);
        $row              = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->agent_rate = $row['rate'];
    }

    public function fetch_role_id()
    {
        $sql  = "SELECT role_id FROM accounts WHERE id = ? LIMIT 0,1";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $row           = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->role_id = $row['role_id'];
    }

    // NEW: fetch multiple roles from account_roles
    public function fetch_role_ids()
    {
        $sql  = "SELECT role_id FROM account_roles WHERE account_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $this->role_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // NEW: assign roles to account (replace existing)
    public function assign_roles($roleIds)
    {
        try {
            $this->con->beginTransaction();

            // Clear existing roles
            $stmt = $this->con->prepare("DELETE FROM account_roles WHERE account_id = ?");
            $stmt->execute([$this->id]);

            // Insert new roles
            if (! empty($roleIds)) {
                $stmt = $this->con->prepare("INSERT INTO account_roles (account_id, role_id) VALUES (?, ?)");
                foreach ($roleIds as $roleId) {
                    $stmt->execute([$this->id, $roleId]);
                }
            }

            $this->con->commit();
            $this->role_ids = $roleIds;

            // Fetch role names for response
            $roles = $this->account_fetch_role_names();

            // Check legacy role_id for super accounts
            $sql  = "SELECT role_id FROM accounts WHERE id = ? LIMIT 1";
            $stmt = $this->con->prepare($sql);
            $stmt->execute([$this->id]);
            $row          = $stmt->fetch(PDO::FETCH_ASSOC);
            $legacyRoleId = $row['role_id'] ?? null;

            // If legacy role_id == 0, add "super account" to roles list
            if ($legacyRoleId === "0" || $legacyRoleId === 0) {
                $roles[] = ["id" => 0, "name" => "super account"];
            }

            return [
                "status"     => "Success",
                "message"    => "Roles updated successfully",
                "account_id" => $this->id,
                "roles"      => $roles,
            ];

        } catch (Exception $e) {
            $this->con->rollBack();
            return ["status" => "Error", "message" => $e->getMessage()];
        }
    }

    // Function to Fetch role names of a given account
    public function account_fetch_role_names()
    {
        $sql = "SELECT r.id, r.name
            FROM account_roles ar
            JOIN roles r ON ar.role_id = r.id
            WHERE ar.account_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // To be named later
    public function fetch_all_account_roles()
    {
        // Start with multi-role assignments
        $sql = "SELECT r.id, r.name
            FROM account_roles ar
            JOIN roles r ON ar.role_id = r.id
            WHERE ar.account_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check legacy role_id
        $sqlLegacy  = "SELECT role_id FROM accounts WHERE id = ? LIMIT 1";
        $stmtLegacy = $this->con->prepare($sqlLegacy);
        $stmtLegacy->execute([$this->id]);
        $row          = $stmtLegacy->fetch(PDO::FETCH_ASSOC);
        $legacyRoleId = $row['role_id'] ?? null;

        if ($legacyRoleId === "0" || $legacyRoleId === 0) {
            $roles[] = ["id" => 0, "name" => "super account"];
        } elseif (! empty($legacyRoleId) && $legacyRoleId != 0) {
            // If legacy role_id is non-zero, include it too
            $sqlRole  = "SELECT id, name FROM roles WHERE id = ? LIMIT 1";
            $stmtRole = $this->con->prepare($sqlRole);
            $stmtRole->execute([$legacyRoleId]);
            $legacyRole = $stmtRole->fetch(PDO::FETCH_ASSOC);
            if ($legacyRole) {
                $roles[] = $legacyRole;
            }
        }

        $this->role_ids = array_column($roles, 'id');
        return $roles;
    }

    // Function to fetch all roles (IDs + names)
    public function fetch_all_roles()
    {
        $sql  = "SELECT id, name FROM roles ORDER BY id ASC";
        $stmt = $this->con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Function to Fetch role ids names
    public function fetch_role_ids_and_names()
    {
        $sql = "SELECT r.id, r.name
            FROM account_roles ar
            JOIN roles r ON ar.role_id = r.id
            WHERE ar.account_id = ?";
        $stmt = $this->con->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
