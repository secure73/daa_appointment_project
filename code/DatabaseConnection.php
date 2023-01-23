<?php
/**
 **by Ali Khorsandfard
 **/
class DatabaseConnection
{

    protected ?string $error;
    protected ?int $affectedRows;
    protected ?int $lastInsertedId;
    private ?PDOStatement $stsment;
    private ?PDO $db;

    /**
     * create new Database connection Instance
     * in case of connection error , 
     * $instance->getError();
     */
    public function __construct()
    {
        $this->error = null;
        $this->affectedRows = null;
        $this->lastInsertedId = null;
        try {
            $options__db = array(
                PDO::ATTR_PERSISTENT    => true,
                PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
            );
            $this->db = new PDO('mysql:host=localhost;dbname=myblog;charset=utf8mb4', 'root', null,$options__db);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * @return PDO 
     * Database Connection or null in case of error
     * in case of null , you can see Error  in created instance : $db->error
     */
    public function db():PDO|null
    {
        return $this->db;
    }

    public function query(string $query): void
    {
        $this->stsment = $this->db->prepare($query);
    }

    public function bind(string $param, mixed $value)
    {
        $type = match (true) {
            is_int($value) => PDO::PARAM_INT,
            is_null($value) => PDO::PARAM_NULL,
            is_bool($value) => PDO::PARAM_BOOL,
            default => PDO::PARAM_STR,
        };
        $this->stsment?->bindValue($param, $value, $type);
    }

    /**
     * @return bool
     * @set error
     * @set affectedRows
     */
    public function execute(): bool
    {
        try {
            $this->stsment ? ($this->stsment->execute()) : ($this->error = 'PDO statement is NULL');
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
        if (!isset($this->error)) {
            $this->affectedRows = $this->stsment->rowCount();
            return true;
        }
        return false;
    }

    public function affectedRows():int|null
    {
        return $this->affectedRows;
    }

    /**
     * @return string|false
     */
    public function lastInsertId(): string|false
    {       
        return $this->db->lastInsertId();
    }

    public function secure(): void
    {
        $this->stsment = null;
        $this->db = null;
    }

    /**
     * @return array|null
     */
    public function fetch(): array|null
    {
        $result = null;
        if ($this->stsment) {
            $result = $this->stsment->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->error = 'PDO Statement is null,please check your connection name or table name';
        }
        return  $result;
    }
}