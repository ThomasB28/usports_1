<?php
/*
 * ServerDataPDO is a class file that wraps data tables SERVER-SIDE processing with PDO (PHP) SQL data abstraction
 * and it provides a simple way to integrate Jquery  data tables with server side databases like SQLite, MySQL and other 
 * PDO supported DB's. It also dynamically renders the Jquery (JAvascript) data tables code and corresponding HTML 
 * (c) Tony Brandao <ab@abrandao.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
date_default_timezone_set('UTC'); // Set default timezone

/* Change these to correspond to your database type (dsn) and access credentials, example below uses sqlite w/o pass */  
$db_dsn='mysql:dbname=usports;host=localhost';  /* corresponds to PDO DSN strings refer to: http://www.php.net/manual/en/pdo.drivers.php */
$db_user='root';   
$db_pass=''; 



//When called directly via the Jquery Ajax Source look for this
//SECURITY NOTE: Consider moving this if..block to another file if security is a concern.
if ( isset($_GET['oDb']) )    //is this being called from datatables ajax?
{

//Do we have an object database info (Serialized) if so expand it\\
//echo $_GET['oDb'];
$d=unserialize(base64_decode($_GET['oDb']));  //NOTE HARDEN  by encrypting
$pdo = new ServerDataPDO($db_dsn,$db_user,$db_pass,$d['sql'],$d['table'],$d['idxcol']);  //construct the object
$result=$pdo->query_datatables(); //now return the JSON Requested data */
echo $result;
exit;
}



class ServerDataPDO
{
	/* UPDATE these variables with valid PDO DSN and credentials to connect to database */
	/* DSN  information http://www.php.net/manual/en/ref.pdo-mysql.connection.php */
	public $db=array( 
					"dsn"=> 'mysql:dbname=usports;host=localhost', 
					"user"=>'root', 
					"pass"=>'',
					"conn"=>null,
					"sql"=>null,
					"table"=>null, /* DB table to use assigned by constructor*/
				    "idxcol"=>null /* Indexed column (used for fast and accurate table cardinality) */
					);

    public static $default_ajax_url=__FILE__; //Defaults to current file name
	
	/* Array of database columns which should be read and sent back to DataTables.dynamically created  */
	public $aColumns = null; // holds SELECT [columns] from SQL query
	public $time_start=null; /* Start timer for metric performance collection */
	
/********************************************************************
    constructor function : called when object is first instantiated
*/
    public function __construct($dsn=null,$user=null,$pass=null,$sql=null, $table=null, $index_col=null) 
	{ 

	$this->db['dsn']= empty($dsn)? $this->db['dsn'] : $dsn;
	$this->db['sql']= empty($sql)? $this->db['sql'] : $sql;
	$this->db['user']= empty($user)? $this->db['user'] : $user;
	$this->db['pass']= empty($pass)? $this->db['pass'] : $pass;
	
	

	/* Create a database connection if $db['conn'] is null*/
	if (empty( $this->db['conn']) )  /* no valid connection? let's make one */
		$this->pdo_conn($this->db['dsn'],$this->db['user'],$this->db['pass']);
		
	/* Start timer for metrics */
	 $this->time_start = microtime(true);
	 
	 /* build the SQL table and columns from the String */
	 if (!empty($sql) )
	   $this->get_SQL_acolumns($sql);
	  
	 /* assign table and index if provided */
	$this->db['idxcol'] = $index_col;
	$this->db['table'] = $table;
	}
	
/********************************************************************
    pdo_conn : Creates a connection to a database vai the PDO (PHP) database abstraction layer
	Refer to http://ca1.php.net/manual/en/pdo.drivers.php  for possible PDO drivers and DSN strings
	Called by the constructor
    @dsn  matches PDO DSN string name for database connection 
    @return  null , sets global $db['conn'] variable
*/
public	function pdo_conn($dsn=null,$username=null,$password=null)
	 {

	 try {
		//echo "[dsn]: $dsn >> Connection: ".$this->db[ 'conn'];
		$this->db['conn'] = new PDO($dsn,$username,$password);    //typical dsn like  'mysql:host=localhost;dbname=testdb';
		
		// Set errormode to exceptions
		$this->db['conn']->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		return true;
	 
	} catch (PDOException $e) {
		$this->fatal_error( "o Database Error!: <strong>".$e->getMessage()."</strong> Dsn= <strong>$dsn </strong><br/>" );
		
	}
	
	} //end of connection function
	
/********************************************************************
    get_SQL_acolumns: Uses a basic SQL Statements SELECT field1,field3,field3 FROM Table 1 and extracts SELECT fields
	NOTES: Column names MUST be explicitly noted , SELECT * FROM is not supported
		   complex SQL statements not currently supported
		   Results of fields names are converted into array in $this->aColumns
	@SQL SQL Statements (basic) to have fields extracted
	@returns false if unable to extract columns otherwise true if  $this->aColumns is successfull
	
*/
    public function get_SQL_acolumns($sql=null,$s1="SELECT",$s2="FROM",$split_on=",")
	{ 

    $pattern = "/$s1(.*?)$s2/i";
	if (preg_match($pattern, $sql, $matches)) 
	 {
	 //  print_r($matches);
	
      $this->aColumns=explode($split_on, $matches[1]);  //return into
 $this->aColumns=array_map('trim',$this->aColumns ); //trim white space	  
	  }
	else
	 {
	 $this->fatal_error("NO SQL columns found in $sql string resulting in <strong>".$result."</strong> Be sure to have $split_on delimited values.");
	return false; //string not found
	}

	  
	}
	
/********************************************************************
 build_jquery_datables:  Static function no object needed to instantiate
  Builds the Javascript JQuery code to call for the database call use this function
	
*/
public static function  build_jquery_datatable($aDBInfo=null,$table_id="datatable1",$ajax_source_url=null,$datatable_properties=null)
{
$js=null;  //Holds the javascript string
$dba=array("a","b");

$ajax_source_url = is_null($ajax_source_url)? basename(__FILE__) : $ajax_source_url;

if (isset($aDBInfo))
  $serializd_db=base64_encode(serialize($aDBInfo));

/* Edit Jquery Here */
$js=  <<<EOT
<!-- Start generated Jquery from $ajax_source_url  --->
<script type="text/JavaScript" charset="utf-8">
$(document).ready(function() {
var editor = new $.fn.dataTable.Editor( {
		"ajax": "$ajax_source_url?oDb='$serializd_db'",
		"table": '#$table_id',
		"fields": [
			{
				"label": "Equipe 1",
				"name": "equipe1"
			},
			{
				"label": "Equipe 2",
				"name": "equipe2"
			},
			{
				"label": "Lieu",
				"name": "lieu"
			},
			{
				"label": "Date",
				"name": "date",
				"dateFormat": "dd-mm-y"
			},
			{
				"label": "Poule",
				"name": "poule"
			}
		]
	} );

	
var oData=$('#$table_id').dataTable( {
"dom": "Tfrtip",
"fnServerData": function ( sSource, aoData, fnCallback ) {
            $.getJSON( sSource, aoData, function (json) {
                oData.fnSettings().oLanguage.sInfoPostFix = '  (processed in '+json.iTime+' s)';
               fnCallback(json)
            })
        },
"iDisplayLength":25,
"bLengthChange": true,
"bProcessing": true,
"bServerSide": true,
"sAjaxSource": "$ajax_source_url?oDb='$serializd_db'",
"bStateSave": true,$datatable_properties 
"bDeferRender": true,
"tableTools": {
			"sRowSelect": "os",
			"aButtons": [
				{ "sExtends": "editor_create", "editor": editor },
				{ "sExtends": "editor_edit",   "editor": editor },
				{ "sExtends": "editor_remove", "editor": editor }
			]
		}
 } );
} ); 
</script>
<!--  End generated Jquery from  $ajax_source_url ---> 
EOT;

return $js;  //returns the completed jquery string
}	
/********************************************************************
    build_html_datatable:   Static function no object needed to instantiate
	Based on the $this->aColumns array it dynamically builds the HTML code for HTML data tables
	@table_id  table_id for Jquery to refer to allow multiple tables
	@columns a comma separated string of column names defaults SQL columns if null
	@returns string containing the completed HTML data tables
*/
public static function  build_html_datatable($columns=null,$table_id="datatable1")
{
$html=null;
$html_columns=null;

//lets extract the columns names from the string
if ( !empty($columns) ) 
	$columns=explode(",",$columns);
else	
  die(" $columns columns array must be  defined, such as col1,col2,col3");
  
//build the header loop through the array and of columns 
$count_cols=count($columns);
foreach($columns as $key=>$val)
  $html_columns.="<th>".trim($val)."</th>\n";
  
$html = <<< EOT
<!-- Start of Generated HTML Datatables structure -->
<table cellpadding='1' cellspacing='1' border='0' class='display' id='$table_id'>
<thead>
<tr>
$html_columns
</tr>
</thead>
<tbody>
<tr><td colspan='$count_cols' class='dataTables_empty'>Loading data from server
</td></tr>
</tbody>
<tfoot>	
<tr>$html_columns</tr>
</tfoot>
</table>

EOT;

return $html;
}		
	
/********************************************************************
    fatal_error : Creates a Server Error to be passed ot calling AJAX page
    @sErrorMessage Error message to be returned to browser
*/
static function fatal_error( $sErrorMessage = '' )
	{
		header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error ' );
		die( $sErrorMessage );
	}
/********************************************************************
query_array : Create an array from a SQL Query string 
@sql  SQL to be executed and returned
@returns $results an array  a PHP array (2D) of results of SQL
*/
function query_array($sql=null)
{
global $db,$debug;
 
try {   
        if ($debug) 
		  $time_start = microtime(true);	 
		
        $stmt = $this->db['conn']->prepare($sql);
        $stmt->execute();
		
		if ($debug){
		 $time =  microtime(true)- $time_start;	
		 echo "<HR>Executed SQL:<strong> $sql </strong> in <strong>$time</strong> s<HR>";
		 }
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC); //PDO::FETCH_NUM | PDO::FETCH_ASSOC
      return  $results ;
} catch (PDOException $e) {
    $this->fatal_error(" Database Error!: <strong>". $e->getMessage() ."</strong> SQL: $sql <br /> Using DSN ".$this->db['dsn']."<br/>");
    die();   die();
}

}	

/********************************************************************
    query_datables : Primary server-side data table processing, builds query and returns encoded json
	reads input from the datables query string parameters and builds SQL
    @returns json_encode JSON encoded results compatible with data tables
*/
function query_datatables()	
{
	/** Paging	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
			intval( $_GET['iDisplayLength'] );
	}
	
	/** Ordering */
	$sOrder = "";
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= "`".$this->aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."` ".
					($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
			}
		}

		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/** Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE (";
		for ( $i=0 ; $i<count($this->aColumns) ; $i++ )
		{
			if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
			{
				$sWhere .= "`".$this->aColumns[$i]."` LIKE '%". $_GET['sSearch'] ."%' OR ";
			}
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ')';
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($this->aColumns) ; $i++ )
	{
		if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE ";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= "`".$this->aColumns[$i]."` LIKE '%".$_GET['sSearch_'.$i]."%' ";
		}
	}
	
	
	/** CORE SQL queries * Get data to display	 */
	$sQuery = "	SELECT  `".str_replace(" , ", " ", implode("`, `", $this->aColumns))."`
		FROM  ".$this->db['table']."
		$sWhere
		$sOrder
		$sLimit
		";
		
	try {	
	
      $aResult  = $this->query_array($sQuery);
	  
	} catch (PDOException $e) {
    print "SQL DAtabase Error!: " . $e->getMessage() . "<br/>";
    die();
}
	
	/* Data set length after filtering */
	//TODO : improve efficiency currently does 2X query  to count total records in query
	$sQuery = "	SELECT COUNT(`".$this->db['idxcol']."`) as totalqry FROM  ".$this->db['table']." $sWhere ";
	$aResultFilterTotal =$this->query_array($sQuery); /* Fetch all of the remaining rows in the result set */
	$iFilteredTotal= $aResultFilterTotal[0]['totalqry'];
	
	/* Total data set length */
	$sQuery = "	SELECT COUNT(`".$this->db['idxcol']."`) as cnt FROM  ".$this->db['table'];
	$aResultTotal = $this->query_array($sQuery);
//	print_r($aResultTotal);
	$iTotal = $aResultTotal[0]['cnt'];

	/*
	 * Output
	 */
	$output = array(
		"sEcho" => intval($_GET['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTime" => (microtime(true)- $this->time_start ),
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => array()
	);
	
	/* Take the Query Result and resturn  JSON encoded String */
	
	foreach ($aResult as $key => $aRow)
	{
		$row = array();
		for ( $i=0 ; $i<count($this->aColumns) ; $i++ )
			$row[] = $aRow[ $this->aColumns[$i] ];

			$output['aaData'][] = $row;
	}
	
	return json_encode( $output );
	
} //end of function

} //end of class	


?>