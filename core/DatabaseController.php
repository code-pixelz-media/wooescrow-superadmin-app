<?php

class DatabaseController
{

    private $conn;

    private $licenseTableName = 'wooescrow_license_keys';
    private $siteurlTableName = 'wooescrow_siteurl';

    public function __construct($dbConnection)
    {

        $this->conn = $dbConnection;
        // $this->deleteTable();
        $this->createTable();
    }
    private function deleteTable()
    {
        // Prepare the SQL statement
        $sql1 = "DROP TABLE IF EXISTS `{$this->licenseTableName}`";
        $this->conn->exec($sql1);

        // Prepare the SQL statement
        // $sql2 = "DROP TABLE IF EXISTS `{$this->siteurlTableName}`";
        // $this->conn->exec($sql2);
    }

    private function createTable()
    {
        $sqlForLisence = "CREATE TABLE IF NOT EXISTS {$this->licenseTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            email_address VARCHAR(255) NOT NULL,
            license_key VARCHAR(255) NOT NULL,
            license_status VARCHAR(50) DEFAULT 'active' NOT NULL,
            total_sites INT(11) NOT NULL,
            active_sites INT(11) NOT NULL,
            created_at DATE NOT NULL,
            valid_until DATE NOT NULL,
            next_renewal DATE NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->conn->exec($sqlForLisence);


        $sqlForSiteUrl = "CREATE TABLE IF NOT EXISTS {$this->siteurlTableName} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            site_url VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'active' NOT NULL,
            license_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE(site_url, license_id),
            FOREIGN KEY (license_id) REFERENCES {$this->licenseTableName} (id)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->conn->exec($sqlForSiteUrl);
    }

    public function insertLicenseKey($data)
    {
        $sql = "INSERT INTO {$this->licenseTableName} (order_id, user_id, email_address, license_key, license_status,total_sites,active_sites, created_at, valid_until, next_renewal)
                VALUES (:order_id, :user_id, :email_address, :license_key, :license_status, :total_sites, :active_sites, :created_at, :valid_until, :next_renewal)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }


    public function fetchAllLicenseKeys()
    {
        $sql = "SELECT * FROM {$this->licenseTableName}";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function fetchLicenseKeyById($id)
    {
        $sql = "SELECT * FROM {$this->licenseTableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateLicenseKey($id, $data)
    {
        try {
            $sql = "UPDATE {$this->licenseTableName} 
                    SET license_key = :license_key,
                        valid_until = :valid_until,
                        next_renewal = :next_renewal
                    WHERE id = :id";

            $stmt = $this->conn->prepare($sql);

            // Bind the parameters
            $stmt->bindParam(':license_key', $data['license_key'], PDO::PARAM_STR);
            $stmt->bindParam(':valid_until', $data['valid_until'], PDO::PARAM_STR);
            $stmt->bindParam(':next_renewal', $data['next_renewal'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the query
            if ($stmt->execute()) {
                return true; // Successfully updated
            } else {
                return false; // Update failed
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage(); // Handle exceptions
        }
    }

    public function deleteLicenseKey($id)
    {
        $sql = "DELETE FROM {$this->licenseTableName} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }


    public function deactivateLicense($id)
    {
        $sql = "UPDATE {$this->siteurlTableName} SET status = :status WHERE license_id = :license_id";
        $stmt = $this->conn->prepare($sql);
        $licenseStatus = 'inactive';
        $stmt->bindParam(':status', $licenseStatus, PDO::PARAM_STR);
        $stmt->bindParam(':license_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function verifyLicenseKey($email, $licenseKey)
    {
        $sql = "SELECT * FROM {$this->licenseTableName} WHERE email_address = :email_address AND license_key = :license_key ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email_address', $email, PDO::PARAM_STR);
        $stmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertSiteIrl($licenseKey, $site_url)
    {
        try {
            $sql = "SELECT * FROM {$this->licenseTableName} WHERE license_key = :license_key";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
            $stmt->execute();
            $existingId = $stmt->fetchColumn();

            $updateActiveSitesSql = "SELECT `active_sites` FROM {$this->licenseTableName} WHERE license_key = :license_key ";
            $stmtActiveSitesSql = $this->conn->prepare($updateActiveSitesSql);
            $stmtActiveSitesSql->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
            $stmtActiveSitesSql->execute();
            $existingActiveSitesSql = $stmtActiveSitesSql->fetchColumn();
            $totalActive_sites = $existingActiveSitesSql + 1;
            if ($existingId !== false) {

                $updateActiveSites = "UPDATE {$this->licenseTableName} 
                          SET active_sites = :active_sites 
                          WHERE license_key = :license_key";

                $insertAsites = $this->conn->prepare($updateActiveSites);
                $insertAsites->bindParam(':active_sites', $totalActive_sites, PDO::PARAM_INT);
                $insertAsites->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
                $insertAsites->execute();


                // Step 2: Insert the value into wooescrow_siteurl table
                $insertSql = "INSERT INTO {$this->siteurlTableName} (site_url, license_id, status) 
        VALUES (:site_url, :license_id, :status)";
                $insertStmt = $this->conn->prepare($insertSql);
                $status = "active";
                $insertStmt->bindParam(':site_url', $site_url, PDO::PARAM_STR);
                $insertStmt->bindParam(':license_id', $existingId, PDO::PARAM_INT);
                $insertStmt->bindParam(':status', $status, PDO::PARAM_STR);
                $insertStmt->execute();


                // Execute the insert statement
                if ($insertStmt->execute() && $insertAsites->execute()) {
                    return true;  // Return true if the insertion was successful
                } else {
                    return false;  // Return false if insertion failed
                }
            } else {
                return "License key not found.";
            }
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function checkLicenseOnDeactivate($email, $licenseKey, $site_url)
    {
        try {
            // Step 1: Retrieve the record from the license table
            $sql = "SELECT * FROM {$this->licenseTableName} WHERE email_address = :email_address AND license_key = :license_key";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':email_address', $email, PDO::PARAM_STR);
            $stmt->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $licenseData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($licenseData) {
                    $totalActive_sites = $licenseData['active_sites'] - 1;


                    $licenseId = $licenseData['id'];
                    $checkStatusSql = "SELECT status FROM {$this->siteurlTableName} WHERE license_id = :license_id AND site_url = :site_url";
                    $statusStmt = $this->conn->prepare($checkStatusSql);
                    $statusStmt->bindParam(':license_id', $licenseId, PDO::PARAM_INT);
                    $statusStmt->bindParam(':site_url', $site_url, PDO::PARAM_STR);

                    if ($statusStmt->execute()) {
                        $statusData = $statusStmt->fetch(PDO::FETCH_ASSOC);

                        if ($statusData && $statusData['status'] === 'active') {
                            $updateActiveSites = "UPDATE {$this->licenseTableName} 
                          SET active_sites = :active_sites 
                          WHERE license_key = :license_key";

                            $insertAsites = $this->conn->prepare($updateActiveSites);
                            $insertAsites->bindParam(':active_sites', $totalActive_sites, PDO::PARAM_INT);
                            $insertAsites->bindParam(':license_key', $licenseKey, PDO::PARAM_STR);
                            $insertAsites->execute();

                            return $licenseData;
                        } else {
                            return "License is not active for the given site URL.";
                        }
                    }
                } else {
                    return "No license found for the given email and license key.";
                }
            }
            return "Error executing license query.";
        } catch (PDOException $e) {
            return "Error: " . $e->getMessage();
        }
    }

    public function fetchAllActiveSites()
    {
        $sql = "SELECT * FROM {$this->siteurlTableName} WHERE status = :status";
        $stmt = $this->conn->prepare($sql);
        $status = 'active';
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function fetchAllADeactiveSites() {
        $sql = "SELECT * FROM {$this->siteurlTableName} WHERE status = :status";
        $stmt = $this->conn->prepare($sql);
        $status = 'inactive';
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
