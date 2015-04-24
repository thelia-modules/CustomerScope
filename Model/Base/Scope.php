<?php

namespace CustomerScope\Model\Base;

use \DateTime;
use \Exception;
use \PDO;
use CustomerScope\Model\CustomerScope as ChildCustomerScope;
use CustomerScope\Model\CustomerScopeQuery as ChildCustomerScopeQuery;
use CustomerScope\Model\Scope as ChildScope;
use CustomerScope\Model\ScopeGroup as ChildScopeGroup;
use CustomerScope\Model\ScopeGroupQuery as ChildScopeGroupQuery;
use CustomerScope\Model\ScopeI18n as ChildScopeI18n;
use CustomerScope\Model\ScopeI18nQuery as ChildScopeI18nQuery;
use CustomerScope\Model\ScopeQuery as ChildScopeQuery;
use CustomerScope\Model\Map\ScopeTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;
use Propel\Runtime\Util\PropelDateTime;

abstract class Scope implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\CustomerScope\\Model\\Map\\ScopeTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the scope_group_id field.
     * @var        int
     */
    protected $scope_group_id;

    /**
     * The value for the entity field.
     * @var        string
     */
    protected $entity;

    /**
     * The value for the entity_class field.
     * @var        string
     */
    protected $entity_class;

    /**
     * The value for the position field.
     * @var        int
     */
    protected $position;

    /**
     * The value for the id field.
     * @var        int
     */
    protected $id;

    /**
     * The value for the created_at field.
     * @var        string
     */
    protected $created_at;

    /**
     * The value for the updated_at field.
     * @var        string
     */
    protected $updated_at;

    /**
     * @var        ScopeGroup
     */
    protected $aScopeGroup;

    /**
     * @var        ObjectCollection|ChildCustomerScope[] Collection to store aggregation of ChildCustomerScope objects.
     */
    protected $collCustomerScopes;
    protected $collCustomerScopesPartial;

    /**
     * @var        ObjectCollection|ChildScopeI18n[] Collection to store aggregation of ChildScopeI18n objects.
     */
    protected $collScopeI18ns;
    protected $collScopeI18nsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    // i18n behavior

    /**
     * Current locale
     * @var        string
     */
    protected $currentLocale = 'en_US';

    /**
     * Current translation objects
     * @var        array[ChildScopeI18n]
     */
    protected $currentTranslations;

    // sortable behavior

    /**
     * Queries to be executed in the save transaction
     * @var        array
     */
    protected $sortableQueries = array();

    /**
     * The old scope value.
     * @var        int
     */
    protected $oldScope;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $customerScopesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection
     */
    protected $scopeI18nsScheduledForDeletion = null;

    /**
     * Initializes internal state of CustomerScope\Model\Base\Scope object.
     */
    public function __construct()
    {
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (Boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (Boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>Scope</code> instance.  If
     * <code>obj</code> is an instance of <code>Scope</code>, delegates to
     * <code>equals(Scope)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        $thisclazz = get_class($this);
        if (!is_object($obj) || !($obj instanceof $thisclazz)) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey()
            || null === $obj->getPrimaryKey())  {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        if (null !== $this->getPrimaryKey()) {
            return crc32(serialize($this->getPrimaryKey()));
        }

        return crc32(serialize(clone $this));
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return Scope The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     *
     * @return Scope The current object, for fluid interface
     */
    public function importFrom($parser, $data)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), TableMap::TYPE_PHPNAME);

        return $this;
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get the [scope_group_id] column value.
     *
     * @return   int
     */
    public function getScopeGroupId()
    {

        return $this->scope_group_id;
    }

    /**
     * Get the [entity] column value.
     *
     * @return   string
     */
    public function getEntity()
    {

        return $this->entity;
    }

    /**
     * Get the [entity_class] column value.
     *
     * @return   string
     */
    public function getEntityClass()
    {

        return $this->entity_class;
    }

    /**
     * Get the [position] column value.
     *
     * @return   int
     */
    public function getPosition()
    {

        return $this->position;
    }

    /**
     * Get the [id] column value.
     *
     * @return   int
     */
    public function getId()
    {

        return $this->id;
    }

    /**
     * Get the [optionally formatted] temporal [created_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCreatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->created_at;
        } else {
            return $this->created_at instanceof \DateTime ? $this->created_at->format($format) : null;
        }
    }

    /**
     * Get the [optionally formatted] temporal [updated_at] column value.
     *
     *
     * @param      string $format The date/time format string (either date()-style or strftime()-style).
     *                            If format is NULL, then the raw \DateTime object will be returned.
     *
     * @return mixed Formatted date/time value as string or \DateTime object (if format is NULL), NULL if column is NULL, and 0 if column value is 0000-00-00 00:00:00
     *
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getUpdatedAt($format = NULL)
    {
        if ($format === null) {
            return $this->updated_at;
        } else {
            return $this->updated_at instanceof \DateTime ? $this->updated_at->format($format) : null;
        }
    }

    /**
     * Set the value of [scope_group_id] column.
     *
     * @param      int $v new value
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setScopeGroupId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->scope_group_id !== $v) {
            // sortable behavior
            $this->oldScope = $this->scope_group_id;

            $this->scope_group_id = $v;
            $this->modifiedColumns[ScopeTableMap::SCOPE_GROUP_ID] = true;
        }

        if ($this->aScopeGroup !== null && $this->aScopeGroup->getId() !== $v) {
            $this->aScopeGroup = null;
        }


        return $this;
    } // setScopeGroupId()

    /**
     * Set the value of [entity] column.
     *
     * @param      string $v new value
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setEntity($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->entity !== $v) {
            $this->entity = $v;
            $this->modifiedColumns[ScopeTableMap::ENTITY] = true;
        }


        return $this;
    } // setEntity()

    /**
     * Set the value of [entity_class] column.
     *
     * @param      string $v new value
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setEntityClass($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->entity_class !== $v) {
            $this->entity_class = $v;
            $this->modifiedColumns[ScopeTableMap::ENTITY_CLASS] = true;
        }


        return $this;
    } // setEntityClass()

    /**
     * Set the value of [position] column.
     *
     * @param      int $v new value
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setPosition($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->position !== $v) {
            $this->position = $v;
            $this->modifiedColumns[ScopeTableMap::POSITION] = true;
        }


        return $this;
    } // setPosition()

    /**
     * Set the value of [id] column.
     *
     * @param      int $v new value
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->id !== $v) {
            $this->id = $v;
            $this->modifiedColumns[ScopeTableMap::ID] = true;
        }


        return $this;
    } // setId()

    /**
     * Sets the value of [created_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setCreatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->created_at !== null || $dt !== null) {
            if ($dt !== $this->created_at) {
                $this->created_at = $dt;
                $this->modifiedColumns[ScopeTableMap::CREATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setCreatedAt()

    /**
     * Sets the value of [updated_at] column to a normalized version of the date/time value specified.
     *
     * @param      mixed $v string, integer (timestamp), or \DateTime value.
     *               Empty strings are treated as NULL.
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function setUpdatedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, '\DateTime');
        if ($this->updated_at !== null || $dt !== null) {
            if ($dt !== $this->updated_at) {
                $this->updated_at = $dt;
                $this->modifiedColumns[ScopeTableMap::UPDATED_AT] = true;
            }
        } // if either are not null


        return $this;
    } // setUpdatedAt()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {


            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : ScopeTableMap::translateFieldName('ScopeGroupId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->scope_group_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : ScopeTableMap::translateFieldName('Entity', TableMap::TYPE_PHPNAME, $indexType)];
            $this->entity = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : ScopeTableMap::translateFieldName('EntityClass', TableMap::TYPE_PHPNAME, $indexType)];
            $this->entity_class = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : ScopeTableMap::translateFieldName('Position', TableMap::TYPE_PHPNAME, $indexType)];
            $this->position = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : ScopeTableMap::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
            $this->id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 5 + $startcol : ScopeTableMap::translateFieldName('CreatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->created_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 6 + $startcol : ScopeTableMap::translateFieldName('UpdatedAt', TableMap::TYPE_PHPNAME, $indexType)];
            if ($col === '0000-00-00 00:00:00') {
                $col = null;
            }
            $this->updated_at = (null !== $col) ? PropelDateTime::newInstance($col, null, '\DateTime') : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 7; // 7 = ScopeTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating \CustomerScope\Model\Scope object", 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
        if ($this->aScopeGroup !== null && $this->scope_group_id !== $this->aScopeGroup->getId()) {
            $this->aScopeGroup = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ScopeTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildScopeQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aScopeGroup = null;
            $this->collCustomerScopes = null;

            $this->collScopeI18ns = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see Scope::setDeleted()
     * @see Scope::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ChildScopeQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            // sortable behavior

            ChildScopeQuery::sortableShiftRank(-1, $this->getPosition() + 1, null, $this->getScopeValue(), $con);
            ScopeTableMap::clearInstancePool();

            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // sortable behavior
            $this->processSortableQueries($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // timestampable behavior
                if (!$this->isColumnModified(ScopeTableMap::CREATED_AT)) {
                    $this->setCreatedAt(time());
                }
                if (!$this->isColumnModified(ScopeTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
                // sortable behavior
                if (!$this->isColumnModified(ScopeTableMap::RANK_COL)) {
                    $this->setPosition(ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con) + 1);
                }

            } else {
                $ret = $ret && $this->preUpdate($con);
                // timestampable behavior
                if ($this->isModified() && !$this->isColumnModified(ScopeTableMap::UPDATED_AT)) {
                    $this->setUpdatedAt(time());
                }
                // sortable behavior
                // if scope has changed and rank was not modified (if yes, assuming superior action)
                // insert object to the end of new scope and cleanup old one
                if (($this->isColumnModified(ScopeTableMap::SCOPE_GROUP_ID)) && !$this->isColumnModified(ScopeTableMap::RANK_COL)) { ChildScopeQuery::sortableShiftRank(-1, $this->getPosition() + 1, null, $this->oldScope, $con);
                    $this->insertAtBottom($con);
                }

            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                ScopeTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aScopeGroup !== null) {
                if ($this->aScopeGroup->isModified() || $this->aScopeGroup->isNew()) {
                    $affectedRows += $this->aScopeGroup->save($con);
                }
                $this->setScopeGroup($this->aScopeGroup);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            if ($this->customerScopesScheduledForDeletion !== null) {
                if (!$this->customerScopesScheduledForDeletion->isEmpty()) {
                    \CustomerScope\Model\CustomerScopeQuery::create()
                        ->filterByPrimaryKeys($this->customerScopesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->customerScopesScheduledForDeletion = null;
                }
            }

                if ($this->collCustomerScopes !== null) {
            foreach ($this->collCustomerScopes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->scopeI18nsScheduledForDeletion !== null) {
                if (!$this->scopeI18nsScheduledForDeletion->isEmpty()) {
                    \CustomerScope\Model\ScopeI18nQuery::create()
                        ->filterByPrimaryKeys($this->scopeI18nsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->scopeI18nsScheduledForDeletion = null;
                }
            }

                if ($this->collScopeI18ns !== null) {
            foreach ($this->collScopeI18ns as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[ScopeTableMap::ID] = true;
        if (null !== $this->id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ScopeTableMap::ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ScopeTableMap::SCOPE_GROUP_ID)) {
            $modifiedColumns[':p' . $index++]  = 'SCOPE_GROUP_ID';
        }
        if ($this->isColumnModified(ScopeTableMap::ENTITY)) {
            $modifiedColumns[':p' . $index++]  = 'ENTITY';
        }
        if ($this->isColumnModified(ScopeTableMap::ENTITY_CLASS)) {
            $modifiedColumns[':p' . $index++]  = 'ENTITY_CLASS';
        }
        if ($this->isColumnModified(ScopeTableMap::POSITION)) {
            $modifiedColumns[':p' . $index++]  = 'POSITION';
        }
        if ($this->isColumnModified(ScopeTableMap::ID)) {
            $modifiedColumns[':p' . $index++]  = 'ID';
        }
        if ($this->isColumnModified(ScopeTableMap::CREATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'CREATED_AT';
        }
        if ($this->isColumnModified(ScopeTableMap::UPDATED_AT)) {
            $modifiedColumns[':p' . $index++]  = 'UPDATED_AT';
        }

        $sql = sprintf(
            'INSERT INTO scope (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'SCOPE_GROUP_ID':
                        $stmt->bindValue($identifier, $this->scope_group_id, PDO::PARAM_INT);
                        break;
                    case 'ENTITY':
                        $stmt->bindValue($identifier, $this->entity, PDO::PARAM_STR);
                        break;
                    case 'ENTITY_CLASS':
                        $stmt->bindValue($identifier, $this->entity_class, PDO::PARAM_STR);
                        break;
                    case 'POSITION':
                        $stmt->bindValue($identifier, $this->position, PDO::PARAM_INT);
                        break;
                    case 'ID':
                        $stmt->bindValue($identifier, $this->id, PDO::PARAM_INT);
                        break;
                    case 'CREATED_AT':
                        $stmt->bindValue($identifier, $this->created_at ? $this->created_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                    case 'UPDATED_AT':
                        $stmt->bindValue($identifier, $this->updated_at ? $this->updated_at->format("Y-m-d H:i:s") : null, PDO::PARAM_STR);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setId($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ScopeTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getScopeGroupId();
                break;
            case 1:
                return $this->getEntity();
                break;
            case 2:
                return $this->getEntityClass();
                break;
            case 3:
                return $this->getPosition();
                break;
            case 4:
                return $this->getId();
                break;
            case 5:
                return $this->getCreatedAt();
                break;
            case 6:
                return $this->getUpdatedAt();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['Scope'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['Scope'][$this->getPrimaryKey()] = true;
        $keys = ScopeTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getScopeGroupId(),
            $keys[1] => $this->getEntity(),
            $keys[2] => $this->getEntityClass(),
            $keys[3] => $this->getPosition(),
            $keys[4] => $this->getId(),
            $keys[5] => $this->getCreatedAt(),
            $keys[6] => $this->getUpdatedAt(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aScopeGroup) {
                $result['ScopeGroup'] = $this->aScopeGroup->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collCustomerScopes) {
                $result['CustomerScopes'] = $this->collCustomerScopes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collScopeI18ns) {
                $result['ScopeI18ns'] = $this->collScopeI18ns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param      string $name
     * @param      mixed  $value field value
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return void
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = ScopeTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @param      mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setScopeGroupId($value);
                break;
            case 1:
                $this->setEntity($value);
                break;
            case 2:
                $this->setEntityClass($value);
                break;
            case 3:
                $this->setPosition($value);
                break;
            case 4:
                $this->setId($value);
                break;
            case 5:
                $this->setCreatedAt($value);
                break;
            case 6:
                $this->setUpdatedAt($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = ScopeTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setScopeGroupId($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setEntity($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setEntityClass($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setPosition($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setId($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setCreatedAt($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setUpdatedAt($arr[$keys[6]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ScopeTableMap::DATABASE_NAME);

        if ($this->isColumnModified(ScopeTableMap::SCOPE_GROUP_ID)) $criteria->add(ScopeTableMap::SCOPE_GROUP_ID, $this->scope_group_id);
        if ($this->isColumnModified(ScopeTableMap::ENTITY)) $criteria->add(ScopeTableMap::ENTITY, $this->entity);
        if ($this->isColumnModified(ScopeTableMap::ENTITY_CLASS)) $criteria->add(ScopeTableMap::ENTITY_CLASS, $this->entity_class);
        if ($this->isColumnModified(ScopeTableMap::POSITION)) $criteria->add(ScopeTableMap::POSITION, $this->position);
        if ($this->isColumnModified(ScopeTableMap::ID)) $criteria->add(ScopeTableMap::ID, $this->id);
        if ($this->isColumnModified(ScopeTableMap::CREATED_AT)) $criteria->add(ScopeTableMap::CREATED_AT, $this->created_at);
        if ($this->isColumnModified(ScopeTableMap::UPDATED_AT)) $criteria->add(ScopeTableMap::UPDATED_AT, $this->updated_at);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(ScopeTableMap::DATABASE_NAME);
        $criteria->add(ScopeTableMap::ID, $this->id);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return   int
     */
    public function getPrimaryKey()
    {
        return $this->getId();
    }

    /**
     * Generic method to set the primary key (id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \CustomerScope\Model\Scope (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setScopeGroupId($this->getScopeGroupId());
        $copyObj->setEntity($this->getEntity());
        $copyObj->setEntityClass($this->getEntityClass());
        $copyObj->setPosition($this->getPosition());
        $copyObj->setCreatedAt($this->getCreatedAt());
        $copyObj->setUpdatedAt($this->getUpdatedAt());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getCustomerScopes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCustomerScope($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getScopeI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addScopeI18n($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setId(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return                 \CustomerScope\Model\Scope Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Declares an association between this object and a ChildScopeGroup object.
     *
     * @param                  ChildScopeGroup $v
     * @return                 \CustomerScope\Model\Scope The current object (for fluent API support)
     * @throws PropelException
     */
    public function setScopeGroup(ChildScopeGroup $v = null)
    {
        if ($v === null) {
            $this->setScopeGroupId(NULL);
        } else {
            $this->setScopeGroupId($v->getId());
        }

        $this->aScopeGroup = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildScopeGroup object, it will not be re-added.
        if ($v !== null) {
            $v->addScope($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildScopeGroup object
     *
     * @param      ConnectionInterface $con Optional Connection object.
     * @return                 ChildScopeGroup The associated ChildScopeGroup object.
     * @throws PropelException
     */
    public function getScopeGroup(ConnectionInterface $con = null)
    {
        if ($this->aScopeGroup === null && ($this->scope_group_id !== null)) {
            $this->aScopeGroup = ChildScopeGroupQuery::create()->findPk($this->scope_group_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aScopeGroup->addScopes($this);
             */
        }

        return $this->aScopeGroup;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('CustomerScope' == $relationName) {
            return $this->initCustomerScopes();
        }
        if ('ScopeI18n' == $relationName) {
            return $this->initScopeI18ns();
        }
    }

    /**
     * Clears out the collCustomerScopes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addCustomerScopes()
     */
    public function clearCustomerScopes()
    {
        $this->collCustomerScopes = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collCustomerScopes collection loaded partially.
     */
    public function resetPartialCustomerScopes($v = true)
    {
        $this->collCustomerScopesPartial = $v;
    }

    /**
     * Initializes the collCustomerScopes collection.
     *
     * By default this just sets the collCustomerScopes collection to an empty array (like clearcollCustomerScopes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCustomerScopes($overrideExisting = true)
    {
        if (null !== $this->collCustomerScopes && !$overrideExisting) {
            return;
        }
        $this->collCustomerScopes = new ObjectCollection();
        $this->collCustomerScopes->setModel('\CustomerScope\Model\CustomerScope');
    }

    /**
     * Gets an array of ChildCustomerScope objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildScope is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildCustomerScope[] List of ChildCustomerScope objects
     * @throws PropelException
     */
    public function getCustomerScopes($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collCustomerScopesPartial && !$this->isNew();
        if (null === $this->collCustomerScopes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCustomerScopes) {
                // return empty collection
                $this->initCustomerScopes();
            } else {
                $collCustomerScopes = ChildCustomerScopeQuery::create(null, $criteria)
                    ->filterByScope($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collCustomerScopesPartial && count($collCustomerScopes)) {
                        $this->initCustomerScopes(false);

                        foreach ($collCustomerScopes as $obj) {
                            if (false == $this->collCustomerScopes->contains($obj)) {
                                $this->collCustomerScopes->append($obj);
                            }
                        }

                        $this->collCustomerScopesPartial = true;
                    }

                    reset($collCustomerScopes);

                    return $collCustomerScopes;
                }

                if ($partial && $this->collCustomerScopes) {
                    foreach ($this->collCustomerScopes as $obj) {
                        if ($obj->isNew()) {
                            $collCustomerScopes[] = $obj;
                        }
                    }
                }

                $this->collCustomerScopes = $collCustomerScopes;
                $this->collCustomerScopesPartial = false;
            }
        }

        return $this->collCustomerScopes;
    }

    /**
     * Sets a collection of CustomerScope objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $customerScopes A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildScope The current object (for fluent API support)
     */
    public function setCustomerScopes(Collection $customerScopes, ConnectionInterface $con = null)
    {
        $customerScopesToDelete = $this->getCustomerScopes(new Criteria(), $con)->diff($customerScopes);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->customerScopesScheduledForDeletion = clone $customerScopesToDelete;

        foreach ($customerScopesToDelete as $customerScopeRemoved) {
            $customerScopeRemoved->setScope(null);
        }

        $this->collCustomerScopes = null;
        foreach ($customerScopes as $customerScope) {
            $this->addCustomerScope($customerScope);
        }

        $this->collCustomerScopes = $customerScopes;
        $this->collCustomerScopesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related CustomerScope objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related CustomerScope objects.
     * @throws PropelException
     */
    public function countCustomerScopes(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collCustomerScopesPartial && !$this->isNew();
        if (null === $this->collCustomerScopes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCustomerScopes) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCustomerScopes());
            }

            $query = ChildCustomerScopeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByScope($this)
                ->count($con);
        }

        return count($this->collCustomerScopes);
    }

    /**
     * Method called to associate a ChildCustomerScope object to this object
     * through the ChildCustomerScope foreign key attribute.
     *
     * @param    ChildCustomerScope $l ChildCustomerScope
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function addCustomerScope(ChildCustomerScope $l)
    {
        if ($this->collCustomerScopes === null) {
            $this->initCustomerScopes();
            $this->collCustomerScopesPartial = true;
        }

        if (!in_array($l, $this->collCustomerScopes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCustomerScope($l);
        }

        return $this;
    }

    /**
     * @param CustomerScope $customerScope The customerScope object to add.
     */
    protected function doAddCustomerScope($customerScope)
    {
        $this->collCustomerScopes[]= $customerScope;
        $customerScope->setScope($this);
    }

    /**
     * @param  CustomerScope $customerScope The customerScope object to remove.
     * @return ChildScope The current object (for fluent API support)
     */
    public function removeCustomerScope($customerScope)
    {
        if ($this->getCustomerScopes()->contains($customerScope)) {
            $this->collCustomerScopes->remove($this->collCustomerScopes->search($customerScope));
            if (null === $this->customerScopesScheduledForDeletion) {
                $this->customerScopesScheduledForDeletion = clone $this->collCustomerScopes;
                $this->customerScopesScheduledForDeletion->clear();
            }
            $this->customerScopesScheduledForDeletion[]= clone $customerScope;
            $customerScope->setScope(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this Scope is new, it will return
     * an empty collection; or if this Scope has previously
     * been saved, it will retrieve related CustomerScopes from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in Scope.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return Collection|ChildCustomerScope[] List of ChildCustomerScope objects
     */
    public function getCustomerScopesJoinCustomer($criteria = null, $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildCustomerScopeQuery::create(null, $criteria);
        $query->joinWith('Customer', $joinBehavior);

        return $this->getCustomerScopes($query, $con);
    }

    /**
     * Clears out the collScopeI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addScopeI18ns()
     */
    public function clearScopeI18ns()
    {
        $this->collScopeI18ns = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collScopeI18ns collection loaded partially.
     */
    public function resetPartialScopeI18ns($v = true)
    {
        $this->collScopeI18nsPartial = $v;
    }

    /**
     * Initializes the collScopeI18ns collection.
     *
     * By default this just sets the collScopeI18ns collection to an empty array (like clearcollScopeI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initScopeI18ns($overrideExisting = true)
    {
        if (null !== $this->collScopeI18ns && !$overrideExisting) {
            return;
        }
        $this->collScopeI18ns = new ObjectCollection();
        $this->collScopeI18ns->setModel('\CustomerScope\Model\ScopeI18n');
    }

    /**
     * Gets an array of ChildScopeI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildScope is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return Collection|ChildScopeI18n[] List of ChildScopeI18n objects
     * @throws PropelException
     */
    public function getScopeI18ns($criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collScopeI18nsPartial && !$this->isNew();
        if (null === $this->collScopeI18ns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collScopeI18ns) {
                // return empty collection
                $this->initScopeI18ns();
            } else {
                $collScopeI18ns = ChildScopeI18nQuery::create(null, $criteria)
                    ->filterByScope($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collScopeI18nsPartial && count($collScopeI18ns)) {
                        $this->initScopeI18ns(false);

                        foreach ($collScopeI18ns as $obj) {
                            if (false == $this->collScopeI18ns->contains($obj)) {
                                $this->collScopeI18ns->append($obj);
                            }
                        }

                        $this->collScopeI18nsPartial = true;
                    }

                    reset($collScopeI18ns);

                    return $collScopeI18ns;
                }

                if ($partial && $this->collScopeI18ns) {
                    foreach ($this->collScopeI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collScopeI18ns[] = $obj;
                        }
                    }
                }

                $this->collScopeI18ns = $collScopeI18ns;
                $this->collScopeI18nsPartial = false;
            }
        }

        return $this->collScopeI18ns;
    }

    /**
     * Sets a collection of ScopeI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $scopeI18ns A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return   ChildScope The current object (for fluent API support)
     */
    public function setScopeI18ns(Collection $scopeI18ns, ConnectionInterface $con = null)
    {
        $scopeI18nsToDelete = $this->getScopeI18ns(new Criteria(), $con)->diff($scopeI18ns);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->scopeI18nsScheduledForDeletion = clone $scopeI18nsToDelete;

        foreach ($scopeI18nsToDelete as $scopeI18nRemoved) {
            $scopeI18nRemoved->setScope(null);
        }

        $this->collScopeI18ns = null;
        foreach ($scopeI18ns as $scopeI18n) {
            $this->addScopeI18n($scopeI18n);
        }

        $this->collScopeI18ns = $scopeI18ns;
        $this->collScopeI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ScopeI18n objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related ScopeI18n objects.
     * @throws PropelException
     */
    public function countScopeI18ns(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collScopeI18nsPartial && !$this->isNew();
        if (null === $this->collScopeI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collScopeI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getScopeI18ns());
            }

            $query = ChildScopeI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByScope($this)
                ->count($con);
        }

        return count($this->collScopeI18ns);
    }

    /**
     * Method called to associate a ChildScopeI18n object to this object
     * through the ChildScopeI18n foreign key attribute.
     *
     * @param    ChildScopeI18n $l ChildScopeI18n
     * @return   \CustomerScope\Model\Scope The current object (for fluent API support)
     */
    public function addScopeI18n(ChildScopeI18n $l)
    {
        if ($l && $locale = $l->getLocale()) {
            $this->setLocale($locale);
            $this->currentTranslations[$locale] = $l;
        }
        if ($this->collScopeI18ns === null) {
            $this->initScopeI18ns();
            $this->collScopeI18nsPartial = true;
        }

        if (!in_array($l, $this->collScopeI18ns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddScopeI18n($l);
        }

        return $this;
    }

    /**
     * @param ScopeI18n $scopeI18n The scopeI18n object to add.
     */
    protected function doAddScopeI18n($scopeI18n)
    {
        $this->collScopeI18ns[]= $scopeI18n;
        $scopeI18n->setScope($this);
    }

    /**
     * @param  ScopeI18n $scopeI18n The scopeI18n object to remove.
     * @return ChildScope The current object (for fluent API support)
     */
    public function removeScopeI18n($scopeI18n)
    {
        if ($this->getScopeI18ns()->contains($scopeI18n)) {
            $this->collScopeI18ns->remove($this->collScopeI18ns->search($scopeI18n));
            if (null === $this->scopeI18nsScheduledForDeletion) {
                $this->scopeI18nsScheduledForDeletion = clone $this->collScopeI18ns;
                $this->scopeI18nsScheduledForDeletion->clear();
            }
            $this->scopeI18nsScheduledForDeletion[]= clone $scopeI18n;
            $scopeI18n->setScope(null);
        }

        return $this;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->scope_group_id = null;
        $this->entity = null;
        $this->entity_class = null;
        $this->position = null;
        $this->id = null;
        $this->created_at = null;
        $this->updated_at = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collCustomerScopes) {
                foreach ($this->collCustomerScopes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collScopeI18ns) {
                foreach ($this->collScopeI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        // i18n behavior
        $this->currentLocale = 'en_US';
        $this->currentTranslations = null;

        $this->collCustomerScopes = null;
        $this->collScopeI18ns = null;
        $this->aScopeGroup = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(ScopeTableMap::DEFAULT_STRING_FORMAT);
    }

    // timestampable behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     ChildScope The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged()
    {
        $this->modifiedColumns[ScopeTableMap::UPDATED_AT] = true;

        return $this;
    }

    // i18n behavior

    /**
     * Sets the locale for translations
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     *
     * @return    ChildScope The current object (for fluent API support)
     */
    public function setLocale($locale = 'en_US')
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Gets the locale for translations
     *
     * @return    string $locale Locale to use for the translation, e.g. 'fr_FR'
     */
    public function getLocale()
    {
        return $this->currentLocale;
    }

    /**
     * Returns the current translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildScopeI18n */
    public function getTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!isset($this->currentTranslations[$locale])) {
            if (null !== $this->collScopeI18ns) {
                foreach ($this->collScopeI18ns as $translation) {
                    if ($translation->getLocale() == $locale) {
                        $this->currentTranslations[$locale] = $translation;

                        return $translation;
                    }
                }
            }
            if ($this->isNew()) {
                $translation = new ChildScopeI18n();
                $translation->setLocale($locale);
            } else {
                $translation = ChildScopeI18nQuery::create()
                    ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                    ->findOneOrCreate($con);
                $this->currentTranslations[$locale] = $translation;
            }
            $this->addScopeI18n($translation);
        }

        return $this->currentTranslations[$locale];
    }

    /**
     * Remove the translation for a given locale
     *
     * @param     string $locale Locale to use for the translation, e.g. 'fr_FR'
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return    ChildScope The current object (for fluent API support)
     */
    public function removeTranslation($locale = 'en_US', ConnectionInterface $con = null)
    {
        if (!$this->isNew()) {
            ChildScopeI18nQuery::create()
                ->filterByPrimaryKey(array($this->getPrimaryKey(), $locale))
                ->delete($con);
        }
        if (isset($this->currentTranslations[$locale])) {
            unset($this->currentTranslations[$locale]);
        }
        foreach ($this->collScopeI18ns as $key => $translation) {
            if ($translation->getLocale() == $locale) {
                unset($this->collScopeI18ns[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * Returns the current translation
     *
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ChildScopeI18n */
    public function getCurrentTranslation(ConnectionInterface $con = null)
    {
        return $this->getTranslation($this->getLocale(), $con);
    }


        /**
         * Get the [title] column value.
         *
         * @return   string
         */
        public function getTitle()
        {
        return $this->getCurrentTranslation()->getTitle();
    }


        /**
         * Set the value of [title] column.
         *
         * @param      string $v new value
         * @return   \CustomerScope\Model\ScopeI18n The current object (for fluent API support)
         */
        public function setTitle($v)
        {    $this->getCurrentTranslation()->setTitle($v);

        return $this;
    }


        /**
         * Get the [description] column value.
         *
         * @return   string
         */
        public function getDescription()
        {
        return $this->getCurrentTranslation()->getDescription();
    }


        /**
         * Set the value of [description] column.
         *
         * @param      string $v new value
         * @return   \CustomerScope\Model\ScopeI18n The current object (for fluent API support)
         */
        public function setDescription($v)
        {    $this->getCurrentTranslation()->setDescription($v);

        return $this;
    }

    // sortable behavior

    /**
     * Wrap the getter for rank value
     *
     * @return    int
     */
    public function getRank()
    {
        return $this->position;
    }

    /**
     * Wrap the setter for rank value
     *
     * @param     int
     * @return    ChildScope
     */
    public function setRank($v)
    {
        return $this->setPosition($v);
    }

    /**
     * Wrap the getter for scope value
     *
     * @param boolean $returnNulls If true and all scope values are null, this will return null instead of a array full with nulls
     *
     * @return    mixed A array or a native type
     */
    public function getScopeValue($returnNulls = true)
    {


        return $this->getScopeGroupId();

    }

    /**
     * Wrap the setter for scope value
     *
     * @param     mixed A array or a native type
     * @return    ChildScope
     */
    public function setScopeValue($v)
    {


        return $this->setScopeGroupId($v);

    }

    /**
     * Check if the object is first in the list, i.e. if it has 1 for rank
     *
     * @return    boolean
     */
    public function isFirst()
    {
        return $this->getPosition() == 1;
    }

    /**
     * Check if the object is last in the list, i.e. if its rank is the highest rank
     *
     * @param     ConnectionInterface  $con      optional connection
     *
     * @return    boolean
     */
    public function isLast(ConnectionInterface $con = null)
    {
        return $this->getPosition() == ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con);
    }

    /**
     * Get the next item in the list, i.e. the one for which rank is immediately higher
     *
     * @param     ConnectionInterface  $con      optional connection
     *
     * @return    ChildScope
     */
    public function getNext(ConnectionInterface $con = null)
    {

        $query = ChildScopeQuery::create();

        $scope = $this->getScopeValue();

        $query->filterByRank($this->getPosition() + 1, $scope);


        return $query->findOne($con);
    }

    /**
     * Get the previous item in the list, i.e. the one for which rank is immediately lower
     *
     * @param     ConnectionInterface  $con      optional connection
     *
     * @return    ChildScope
     */
    public function getPrevious(ConnectionInterface $con = null)
    {

        $query = ChildScopeQuery::create();

        $scope = $this->getScopeValue();

        $query->filterByRank($this->getPosition() - 1, $scope);


        return $query->findOne($con);
    }

    /**
     * Insert at specified rank
     * The modifications are not persisted until the object is saved.
     *
     * @param     integer    $rank rank value
     * @param     ConnectionInterface  $con      optional connection
     *
     * @return    ChildScope the current object
     *
     * @throws    PropelException
     */
    public function insertAtRank($rank, ConnectionInterface $con = null)
    {
        $maxRank = ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con);
        if ($rank < 1 || $rank > $maxRank + 1) {
            throw new PropelException('Invalid rank ' . $rank);
        }
        // move the object in the list, at the given rank
        $this->setPosition($rank);
        if ($rank != $maxRank + 1) {
            // Keep the list modification query for the save() transaction
            $this->sortableQueries []= array(
                'callable'  => array('\CustomerScope\Model\ScopeQuery', 'sortableShiftRank'),
                'arguments' => array(1, $rank, null, $this->getScopeValue())
            );
        }

        return $this;
    }

    /**
     * Insert in the last rank
     * The modifications are not persisted until the object is saved.
     *
     * @param ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     *
     * @throws    PropelException
     */
    public function insertAtBottom(ConnectionInterface $con = null)
    {
        $this->setPosition(ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con) + 1);

        return $this;
    }

    /**
     * Insert in the first rank
     * The modifications are not persisted until the object is saved.
     *
     * @return    ChildScope the current object
     */
    public function insertAtTop()
    {
        return $this->insertAtRank(1);
    }

    /**
     * Move the object to a new rank, and shifts the rank
     * Of the objects inbetween the old and new rank accordingly
     *
     * @param     integer   $newRank rank value
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     *
     * @throws    PropelException
     */
    public function moveToRank($newRank, ConnectionInterface $con = null)
    {
        if ($this->isNew()) {
            throw new PropelException('New objects cannot be moved. Please use insertAtRank() instead');
        }
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }
        if ($newRank < 1 || $newRank > ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con)) {
            throw new PropelException('Invalid rank ' . $newRank);
        }

        $oldRank = $this->getPosition();
        if ($oldRank == $newRank) {
            return $this;
        }

        $con->beginTransaction();
        try {
            // shift the objects between the old and the new rank
            $delta = ($oldRank < $newRank) ? -1 : 1;
            ChildScopeQuery::sortableShiftRank($delta, min($oldRank, $newRank), max($oldRank, $newRank), $this->getScopeValue(), $con);

            // move the object to its new rank
            $this->setPosition($newRank);
            $this->save($con);

            $con->commit();

            return $this;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Exchange the rank of the object with the one passed as argument, and saves both objects
     *
     * @param     ChildScope $object
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     *
     * @throws Exception if the database cannot execute the two updates
     */
    public function swapWith($object, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }
        $con->beginTransaction();
        try {
            $oldScope = $this->getScopeValue();
            $newScope = $object->getScopeValue();
            if ($oldScope != $newScope) {
                $this->setScopeValue($newScope);
                $object->setScopeValue($oldScope);
            }
            $oldRank = $this->getPosition();
            $newRank = $object->getPosition();

            $this->setPosition($newRank);
            $object->setPosition($oldRank);

            $this->save($con);
            $object->save($con);
            $con->commit();

            return $this;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Move the object higher in the list, i.e. exchanges its rank with the one of the previous object
     *
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     */
    public function moveUp(ConnectionInterface $con = null)
    {
        if ($this->isFirst()) {
            return $this;
        }
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }
        $con->beginTransaction();
        try {
            $prev = $this->getPrevious($con);
            $this->swapWith($prev, $con);
            $con->commit();

            return $this;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Move the object higher in the list, i.e. exchanges its rank with the one of the next object
     *
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     */
    public function moveDown(ConnectionInterface $con = null)
    {
        if ($this->isLast($con)) {
            return $this;
        }
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }
        $con->beginTransaction();
        try {
            $next = $this->getNext($con);
            $this->swapWith($next, $con);
            $con->commit();

            return $this;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Move the object to the top of the list
     *
     * @param     ConnectionInterface $con optional connection
     *
     * @return    ChildScope the current object
     */
    public function moveToTop(ConnectionInterface $con = null)
    {
        if ($this->isFirst()) {
            return $this;
        }

        return $this->moveToRank(1, $con);
    }

    /**
     * Move the object to the bottom of the list
     *
     * @param     ConnectionInterface $con optional connection
     *
     * @return integer the old object's rank
     */
    public function moveToBottom(ConnectionInterface $con = null)
    {
        if ($this->isLast($con)) {
            return false;
        }
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ScopeTableMap::DATABASE_NAME);
        }
        $con->beginTransaction();
        try {
            $bottom = ChildScopeQuery::create()->getMaxRankArray($this->getScopeValue(), $con);
            $res = $this->moveToRank($bottom, $con);
            $con->commit();

            return $res;
        } catch (Exception $e) {
            $con->rollback();
            throw $e;
        }
    }

    /**
     * Removes the current object from the list (moves it to the null scope).
     * The modifications are not persisted until the object is saved.
     *
     * @return    ChildScope the current object
     */
    public function removeFromList()
    {
        // check if object is already removed
        if ($this->getScopeValue() === null) {
            throw new PropelException('Object is already removed (has null scope)');
        }

        // move the object to the end of null scope
        $this->setScopeValue(null);

        return $this;
    }

    /**
     * Execute queries that were saved to be run inside the save transaction
     */
    protected function processSortableQueries($con)
    {
        foreach ($this->sortableQueries as $query) {
            $query['arguments'][]= $con;
            call_user_func_array($query['callable'], $query['arguments']);
        }
        $this->sortableQueries = array();
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {

    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {

    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
