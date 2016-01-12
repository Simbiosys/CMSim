<?php	/**	* Singular's Abstract Database Provider	*/	namespace Singular;	/** 		* Singular's Abstract Database Provider Class 		*/	abstract class DataBaseProvider {		/** @var Object|null $resource Database resource. */		protected $resource = NULL;		/**		* Connects to a database		*		* @param string $host Database server.		* @param string $user Database user.		* @param string $pass Database password.		* @param string $dbname Database name.    *		* @return void		*/		public abstract function connect($host, $user, $pass, $dbname);		/**		* Disconnect from a database.    *		* @return void		*/		public abstract function disconnect();		/**		* Returns the number of error.    *		* @return integer		*/		public abstract function get_error_number();		/**		* Returns the error information.    *		* @return Object		*/		public abstract function get_error();		/**		* Performs a query.		*		* @param string $q Query to execute.    *		* @return void		*/		public abstract function query($q);		/**		* Returns the number of rows.		*		* @param Object $resource Database resource.    *		* @return integer		*/		public abstract function number_of_rows($resource);		/**		* Returns the array from a resource.		*		* @param Object $resource Database resource.    *		* @return Array		*/		public abstract function get_array($resource);		/**		* Checks if there is a connection to the database.    *		* @return boolean		*/		public abstract function is_there_connection();		/**		* Escapes a parameter.		*		* @param string $var Parameter to escape.    *		* @return string		*/		public abstract function escape($var);		/**		* Returns the id.    *		* @return string		*/		public abstract function get_id();		/**		* Changes the selected database.		*		* @param string $database Database to set.    *		* @return void		*/		public abstract function change_database($database);		/**		* Sets a specified charset.		*		* @param string $charset Charset to set.    *		* @return void		*/		public abstract function set_charset($charset);		/**		* Checks that a table exists, if not it is created.		*		* @param string $table Table's name.		* @param Array $fields Table's fields.		* @param string $primary_key Table's primary key.    *		* @return void		*/		public abstract function check_table($table, $fields, $primary_key);		/**		* Checks that one table fields exist, if not they are created.		*		* @param string $table Table's name.		* @param Array $fields Table's fields.		* @param string $primary_key Table's primary key.    *		* @return void		*/		public abstract function check_fields($table, $fields, $primary_key);		/**		* Format data according to the field structure		*		* @param Array $data Data to format.		* @param Array $fields_structure Field structure.    *		* @return Array		*/		public abstract function format_fields($data, $fields_structure);		/**		* Gets the query from a model.		*		* @param Array $model Model.		* @param boolean $with_dependencies True to apply dependencies.    *		* @return string		*/		public abstract function get_query($model, $query_fields, $with_dependencies);		/**		* Gets the number of occurrences for a condition.		*		* @param Array $model Model.		* @param Array $condition Condition to search.    *		* @return string		*/		public abstract function get_count($model, $condition = NULL);		/**		* Gets the query for a condition.		*		* @param Array $model Model.		* @param Array $condition Condition to search.    *		* @return string		*/		public abstract function get_query_by_condition($model, $query_fields, $condition);		/**		* Gets the query for a key-value pair.		*		* @param Array $model Model.		* @param string $field Field's name.		* @param string $value Field's value.    *		* @return string		*/		public abstract function get_query_by_value($model, $field, $value);		/**		* Gets the filter of a model.		*		* @param Array $model Model.    *		* @return string		*/		public abstract function get_filter($model);		/**		* Gets the default filter.		*		* @return string		*/		public abstract function get_default_filter();		/**		* Gets the default deletion.		*		* @param Array $model Model.		*		* @return string		*/		public abstract function get_default_deletion($model);		/**		* Gets the order criteria of a model.		*		* @param Array $model Model.    *		* @return string		*/		public abstract function get_order($model);		/**		* Gets the update query of a model.		*		* @param Array $table Table's name.		* @param Array $columns Columns to update.		* @param Array $id Register's identifier.    *		* @return string		*/		public abstract function get_update($table, $columns, $id);		/**		* Gets the insert query of a model.		*		* @param Array $table Table's name.		* @param Array $columns Columns to update.    *		* @return string		*/		public abstract function get_insert($table, $columns);		/**		* Gets the delete query of a model.		*		* @param Array $table Table's name.		* @param Array $id Register's identifier.    *		* @return string		*/		public abstract function get_delete($table, $id);		/**		* Gets the delete query of a model.		*		* @param Array $table Table's name.		* @param string $condition Condition to apply.    *		* @return string		*/		public abstract function get_delete_by_condition($table, $condition);		/**		* Gets the NULL identifier for the database.		*		* @return string		*/		public abstract function get_null();		/**		* Gets the NOW function.		*		* @return string		*/		public abstract function get_now();	}