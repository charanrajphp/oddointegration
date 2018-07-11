<?php
//author charan
//this is not for commerical use. for knowledge sharing i am sharing, don't use for commrical purpose will take charge on this
//in order to run this sciprt first import category and sub_category .. base on that we can import
// For Develope environment enable this following two lines to debud
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Login information
$url = 'odoo_url';
$url_auth = $url . '/xmlrpc/2/common';
$url_exec = $url . '/xmlrpc/2/object';

$db = '4D_LIVE';
$username = 'odoo_username';
$password = 'odoo_password';



require_once('ripcord/ripcord.php');

echo "---hi---";
// Login
$common = ripcord::client($url_auth);
$uid = $common->authenticate($db, $username, $password, array());

print("<p>Your current user id is ''</p>");
echo '<pre>';print_r($uid);

$models = ripcord::client($url_exec);
echo '<pre>';print_r($models);

$products = $models->execute_kw($db, $uid, $password,
    'product.template', 'check_access_rights',
    array('read'), array('raise_exception' => false));

echo '<pre>';print_r($products);

$records = $models->execute_kw($db, $uid, $password, 'product.template', 'search_read', 
array(
  array(
    array('qty_available', '>', 0),
    array('company_id', '=', 13),
  ),
), array(
  //'fields' => array('code', 'display_name', 'qty_available','description','categ_id','name','list_price','company_id','attribute_line_ids','product_variant_count','product_variant_ids','attribute_value_ids '),
  'fields' => array('code', 'display_name', 'qty_available','description','categ_id','name','list_price','company_id','attribute_line_ids','product_variant_count','product_variant_ids','image','image_small','image_medium','attribute_value_ids'),
  //'fields' => array('code', 'display_name', 'qty_available','description','categ_id','name','list_price','company_id','attribute_line_ids','product_variant_count','product_variant_ids','attribute_value_ids'),
  'limit'=> 5
  //'limit'=> NULL
  //'fields' => array('code', 'display_name', 'qty_available','categ_id','description','image_small','__last_update','image','list_price','standard_price','image_medium')
)
);
echo "======================Here ===============";


$color_sizes = $models->execute_kw($db, $uid, $password, 'product.attribute.value', 'search_read', 
array(
  array(
  ),
), array(
  'fields' => array('attribute_id', 'name'),
  //'limit'=> 5
  'limit'=> NULL
  //'fields' => array('code', 'display_name', 'qty_available','categ_id','description','image_small','__last_update','image','list_price','standard_price','image_medium')
)
);
//print_r($color_sizes);
$varaints = array();
foreach($color_sizes as $color_size){
	$varaints[$color_size['id']] = array($color_size['attribute_id'][1] => $color_size['name']);
}

$servername = "yourhost";
//$username = "yourusername";$password_db = "yourdbpassword";
$username = "root";$password_db = "";
$dbname = "yourdbname";
$conn = mysqli_connect($servername, $username, $password_db, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}else{
	echo "=======================connection sucess===============";
}

	// exit;
//echo '<pre>';print_r($records);exit;
$cat_sub_category = array();
$cat_sub_category_oddo = array();
$category_oddo = array();
echo $sql = "SELECT sub_category_id, category,odoo_id FROM sub_category";
$result = $conn->query($sql);

$sql2 = "SELECT category_id,odoo_id FROM `category`  ";
$result2 = $conn->query($sql2);

if ($result2->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //echo "id: " . $row["sub_category_id"]. " - Name: " . $row["category"]. " " . $row["odoo_id"]. "<br>";
		$cat_sub_category[$row["sub_category_id"]] = $row["category"];
		$cat_sub_category_oddo[$row["sub_category_id"]] = $row["odoo_id"];
		//$cat_sub_category_oddo[$row["odoo_id"]] = $row["category"];
		
    }
	while($row2 = $result2->fetch_assoc()) {
		$category_oddo[$row2["category_id"]] = $row2["odoo_id"];
		
    }
	$product_cat_id = '';
	$product_sub_cat_id = '';
	$product_i_sql = "INSERT INTO bhroutle_bhroutlet.product (discount_type,added_by,title,tag,description,category,sub_category ,odoo_id, status, sale_price,current_stock,odee_image,odee_image_small,odee_image_medium,options,num_of_imgs) VALUES ";
	
	//echo '<pre>';print_r($records);exit;
	foreach($records as $record){	
		//if(!is_array($parent_id))
		{	
		
			$odoo_id = $record['id'];
			$title = $record['display_name'];
			$tag = $record['name'];
			echo "=======================odoo_id sucess===============".$odoo_id;
			//product_variant_ids
			$product_variant_ids = $record['product_variant_ids'];
			print_r($product_variant_ids);
			$product_options = null;
			$product_options_size = null;
			$product_options_color = null;
			if(count($product_variant_ids) > 0){
				//$products_v = $models->execute_kw($db, $uid, $password,'product.product', 'read', array($product_variant_ids));
				$products_v = $models->execute_kw($db, $uid, $password,
				'product.product', 'read', array($product_variant_ids));
				//'product.product', 'read', array($product_variant_ids),'fields' => array('name', 'id'));
		
				//echo '<pre>';print_r($products_v);exit;
				//
				//var_dump($attribute_value_ids);
				foreach($products_v as $products_vv){
					$attribute_value_id  = $products_vv['attribute_value_ids'];
					echo '<pre>';print_r($attribute_value_id);
					foreach($attribute_value_id as $attribute_value){
						$attribute_value_id_varaints = $varaints[$attribute_value];
						//var_dump($attribute_value_id_varaints);
						if (array_key_exists("Size",$attribute_value_id_varaints)){							
							$product_options_size[] = $attribute_value_id_varaints['Size'];
						}else{
							$product_options_color[] = $attribute_value_id_varaints['Color'];
						}
					}
					unset($attribute_value_id);unset($attribute_value_id_varaints);
				}
			}
			$product_options_size = array_unique($product_options_size);
			$product_options_color = array_unique($product_options_color);
			echo '<pre>';print_r($product_options_size);			
			echo '<pre>';print_r($product_options_color);
			$product_options_1 = null;
			if(!empty($product_options_size)){
				//echo $product_options_size_str = implode(',',$product_options_size);
				echo $product_options_size_str = implode('","',$product_options_size);
				//echo $product_options_size_str .= '"'.$product_options_size_str.'"';
				echo $product_options_1 = '{"no":"0","title":"Size","name":"choice_0","type":"single_select","option":["'.$product_options_size_str.'"]}';
			}
			if(!empty($product_options_color)){				
				//echo $product_options_color_str = implode(',',$product_options_color);
				$product_options_color_str = implode('","',$product_options_color);
				//echo $product_options_color_str .= '"'.$product_options_color_str.'"';
				if(!empty($product_options_size)){
					echo $product_options_1 .= ',{"no":"1","title":"Color","name":"choice_1","type":"single_select","option":["'.$product_options_color_str.'"]}';
				}else{
					echo $product_options_1 = '{"no":"0","title":"Color","name":"choice_0","type":"single_select","option":["'.$product_options_color_str.'"]}';
				}
			}
			if(!empty($product_options_1)){
				$product_options = '['.$product_options_1.']';
			}
			echo '<br />'.$product_options;
			
			//exit;
			unset($attribute_value_id);unset($product_options_size);unset($product_options_color);
			$description = $record['description'];
			$categ_id_array = $record['categ_id'];
			$sale_price = $record['list_price'];
			$current_stock = $record['qty_available'];
			$odee_image = '';//$record['image'];
			$odee_image_small = '';//$record['image_small'];
			$odee_image_medium = '';//$record['image_medium'];
                    
					
			$categ_id = $categ_id_array[0];
			$categ_id_oddo = '';
			$sub_categ_id_oddo = '';
			echo '<br />';echo "----debug start===";echo '<br />';
			if(!empty($categ_id)){
				$a = array_search($categ_id,$cat_sub_category_oddo);
				//$a = in_array($categ_id,$cat_sub_category_oddo);
				//var_dump($categ_id);var_dump($a);
				//echo '<pre>';print_r($cat_sub_category);echo '<pre>';print_r($cat_sub_category_oddo);echo '<pre>';print_r($category_oddo);
				$b = $cat_sub_category[$a];
				var_dump($b);
				if(!empty($b)){
					//var_dump($b);print_r($category_oddo);
					$categ_id_oddo = $b;//array_search($b,$category_oddo);
					$sub_categ_id_oddo = $a;
				}else{
					//var_dump($a);var_dump($b);
					$categ_id_oddo = array_search($categ_id,$category_oddo);;
					$sub_categ_id_oddo = '';
				}
			}
			//echo '<br />';echo $categ_id_oddo;echo '<br />';echo $sub_categ_id_oddo;exit;
			$resultset_1 = $conn->query("SELECT product_id FROM `product` WHERE `odoo_id` ='".$odoo_id."' ") or die(mysqli_error());
			$row = $resultset_1->fetch_assoc();
			$added_by = '{"type":"admin","id":"1"}' ;
			//if($count == 0){
			if($resultset_1->num_rows > 0){
				echo "in update product";echo "<br />";
				$product_u_sql = "UPDATE bhroutle_bhroutlet.product SET discount_type = 'percent' ,`added_by` = '".$added_by."' ,title='" .$title. "', tag='" .$tag. "' ,description='" .$description. "',category='" .$categ_id_oddo. "' ,sub_category='" .$sub_categ_id_oddo. "',sale_price = '" .$sale_price. "', current_stock = '" .$current_stock. "',odee_image = '" .$odee_image. "',odee_image_small = '" .$odee_image_small. "',odee_image_medium = '" .$odee_image_medium. "' ,options = '" .$product_options. "' ,num_of_imgs = '1' WHERE odoo_id=" .$odoo_id. "";
				echo $product_u_sql;echo '<br />';
				$conn->query($product_u_sql);
				$product_id = $row['product_id'];
				$odee_image = $record['image'];
				$odee_image_small = $record['image_small'];
				$odee_image_medium = $record['image_medium'];
				if(!empty($product_id)){
					echo getcwd() . "\n";
                    $dir = getcwd().'/beta/uploads/product_image/';
                    $name1 = 'product_'.$product_id.'_1_thumb';
                    $img1 = $odee_image_medium;
                    $img1 = str_replace('data:image/jpeg;base64,', '', $img1);
                    $data1 = base64_decode($img1);
                    //$file = UPLOAD_DIR . $name . '.png';
                    echo $file1 = $dir . $name1 . '.jpg';
                    $success1 = file_put_contents($file1, $data1);
					
					$name2 = 'product_'.$product_id.'_1';
					$img2 = $odee_image;
                    $img2 = str_replace('data:image/jpeg;base64,', '', $img2);
                    $data2 = base64_decode($img2);                    
                    echo $file2 = $dir . $name2 . '.jpg';
                    $success2 = file_put_contents($file2, $data2);
				}
			}else{			   			  
				echo "in insert product";echo "<br />";
				$resultset_2 = $product_i_sql .= "('percent','".$added_by."' , '" .$title. "' ,'" .$tag. "' ,'" .$description. "' ,'" .$categ_id_oddo. "' ,'" .$sub_categ_id_oddo. "' , '" .$odoo_id. "' ,'ok','" .$sale_price. "', '" .$current_stock. "', '" .$odee_image. "', '" .$odee_image_small. "', '" .$odee_image_medium. "' , '" .$product_options. "' ,'1'),";
				echo $product_i_sql;echo '<br />';
				$conn->query($product_i_sql);
				$product_id =  $conn->insert_id;//$row['product_id'];
				$odee_image = $record['image'];
				$odee_image_small = $record['image_small'];
				$odee_image_medium = $record['image_medium'];
				if(!empty($product_id)){
					echo getcwd() . "\n";
                    $dir = getcwd().'/beta/uploads/product_image/';
                    $name1 = 'product_'.$product_id.'_1_thumb';
                    $img1 = $odee_image_medium;
                    $img1 = str_replace('data:image/jpeg;base64,', '', $img1);
                    $data1 = base64_decode($img1);
                    //$file = UPLOAD_DIR . $name . '.png';
                    echo $file1 = $dir . $name1 . '.jpg';
                    $success1 = file_put_contents($file1, $data1);
					
					$name2 = 'product_'.$product_id.'_1';
					$img2 = $odee_image;
                    $img2 = str_replace('data:image/jpeg;base64,', '', $img2);
                    $data2 = base64_decode($img2);                    
                    echo $file2 = $dir . $name2 . '.jpg';
                    $success2 = file_put_contents($file2, $data2);
				}
			}
			
			unset($resultset_1);unset($resultset_2);
			unset($product_u_sql);unset($odoo_id);unset($title);
			unset($categ_id_array);unset($categ_id);unset($categ_id_oddo);unset($sub_categ_id_oddo);
			unset($tag);unset($description);unset($product_i_sql);unset($product_id);
		}
	}
	//insert product
	$product_i_sql = rtrim($product_i_sql, ',');
	echo $product_i_sql;echo '<br >';
	$conn->query($product_i_sql);
	unset($product_i_sql);
} 
else {
    echo "0 results";
}

unset($cat_sub_category);
unset($cat_sub_category_oddo);
exit;
/*$partner_field=$models->execute_kw($db, $uid, $password,
                           'product.template', 'fields_get',array(),
                            array('attributes' => array('string', 'help', 'type')));
							
echo '<pre>';print_r($partner_field);
$a = array_keys($partner_field);
print_r($a);
exit;	*/						
$records = $models->execute_kw($db, $uid, $password,
    'product.product', 'search',
    array(array(array('qty_available', '>', 0),
                )),
    array('offset'=>0, 'limit'=>5));
//array('offset'=>10, 'limit'=>5)
echo '<pre>';print_r($records);
exit;

$customer_ids = $models->execute_kw(
    $db, // DB name
    $uid, // User id, user login name won't work here
    $password, // User password
    'product.template', // Model name
    'search', // Function name
    array( // Search domain
        
        )
 );

echo '<pre>';print_r($customer_ids);

exit;

$customers = $models->execute_kw($db, $uid, $password, 'purchase.order',
    'read',  // Function name
    array($customer_ids), // An array of record ids
    array('fields'=>array('name', 'businessid')) // Array of wanted fields
);
echo '<h4>Output example</h4>';

echo '<pre>';print_r($customers);

print("<p><strong>Found customers:</strong><br/>");
foreach ($customers as &$customer){
    print("${customer[name]} ${customer[businessid]}<br/>");
}
print("</p>");



?>
