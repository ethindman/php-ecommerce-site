<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admins extends CI_Controller {

	public function index()
	{
		$this->load->view('admin/index');
	}

	public function dashboard($page = 1)
	{
		if($this->session->userdata('user_level') == 'admin')
		{
			$orders = $this->admin->retrieveAll();
			$page_order_data = array();
	    $count = 5 * $page - 5;
	    for ($i=$count;$i<count($orders);$i++)
	    {
	      array_push($page_order_data, $orders[$i]);
	      $count++;
	      if ($count >= 5 * $page)
	      {
	        break;
	      }
	    }
				$this->load->view('admin/dashboard', array('orders' => $page_order_data, 'total_orders' => count($orders)));
		}
		else{
			$this->load->view('admin');
		}		
	}

	public function retrieveOneOrder()
	{
		$order = $this->admin->retrieveOneOrder($this->input->post('id'));
		$products = $this->admin->retrieveOrderProduct($this->input->post('id'));
		$this->load->view('admin/order_detail', array('order'=>$order, 'products'=>$products));
	}

	public function status()
	{
		$this->admin->updateStatus($this->input->post());
		redirect('admins/dashboard');
	}

	public function products($page = 1)
	{
		if($this->session->userdata('user_level') == 'admin')
		{
			$products = $this->admin->retrieveAllProducts();
			$page_product_data = array();
	    $count = 5 * $page - 5;
	    for ($i=$count;$i<count($products);$i++)
	    {
	      array_push($page_product_data, $products[$i]);
	      $count++;
	      if ($count >= 5 * $page)
	      {
	        break;
	      }
	    }
				$this->load->view('admin/products', array('products' => $page_product_data, 'total_products' => count($products) ));
		}
		else
		{
			redirect('admin');
		}
	}

	public function edit()
	{
		if($this->session->userdata('user_level') == 'admin')
		{	
			$result = $this->admin->retrieveOneItem($this->input->post('id'));
			$this->load->view('admin/edit_product', array('item'=>$result));
		}
		else
		{
			redirect('admin');
		}
	}

	public function addItem()
	{
		if($this->session->userdata('user_level') == 'admin')
		{
			$this->load->view('admin/newProduct');
		}
		else
		{
			redirect('admin');
		}
	}

	public function createNew()
	{
		$this->admin->createNew($this->input->post());
		redirect('admins/products');
	}

	public function updateItem()
	{
		$this->admin->updateItem($this->input->post());
		redirect('admins/products');
	}

	public function delete($id)
	{
		$this->admin->delete($id);
		redirect('admins/products');
	}

	public function search_products()
	{
		$result = $this->admin->search_products($this->input->post('search'));
		$this->load->view('admin/products', array('products'=>$result));
	}
	
	public function search_orders()
	{
		$result = $this->admin->search_orders($this->input->post('search'));
		$this->load->view('admin/dashboard', array('orders'=> $result));
	}

	public function login()
	{
		$email = $this->input->post('email');
		$password = $this->input->post('password');
		$user = $this->admin->retreiveOneUser($email);
		if($user && $user['password'] == $password)
		{
			$user = array(
				'user_id' => $user['id'],
				'user_email' => $user['email'],
				'user_level' => $user['user_level'],
				'is_logged_in' => true
				);
			$this->session->set_userdata($user);
			redirect('admins/dashboard');
		}
		else
		{
			$this->session->set_flashdata("error", "Invalid email or password!");
			redirect('admins');
		}
	}

	public function logoff()
	{
		$this->session->sess_destroy();
		redirect('admin');
	}

	public function upload()
	{
		$this->load->view('admin/upload_photo');
	}

	public function upload_photo()
	{
		$id = $this->input->post('item_id');
		function randomKey() 
		{
		  $key = '';  
		  for ($i=0; $i < 10 ; $i++) 
		  { 
		  	$key = $key.rand(0,100);
		  }
		  return $key;
		}
		$rand = randomKey();
		$target_dir = "./assets/img/lg/";
		$originalName = basename($_FILES["fileToUpload"]["name"]);
		$imageFileType = pathinfo($originalName,PATHINFO_EXTENSION);
		$target_file = $target_dir . $rand . "." . $imageFileType;
		// $newImageName = $rand . "." . $imageFileType;
		$uploadOk = 1;
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) 
		{
	    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	    if($check !== false) 
	    {
	      echo "File is an image - " . $check["mime"] . ".";
	      $uploadOk = 1;
	    } 
	    else 
	    {
	      echo "File is not an image.";
	      $uploadOk = 0;
	    }
		}
		// Check if file already exists
		if (file_exists($target_file)) 
		{
		  echo "Sorry, file already exists.";
		  $uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 5000000) 
		{
		  echo "Sorry, your file is too large.";
		  $uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) 
		{
		  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
		  $uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) 
		{
		  echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} 
		else 
		{
	    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) 
	    {
        $img_name = $rand.".".$imageFileType;
       	$result = $this->admin->uploadPhoto($img_name, $id);
      	if($result)
       	{
       		redirect('admins/products');
       	}
       	else
       	{
       		die('fail!');
       	}
	    } 
	    else 
	    {
		    echo "Sorry, there was an error uploading your file.";
		  }
		}
	}
}

//end of main controller