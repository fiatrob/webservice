<?php
  /**
   * RESTful API webservice script
   * ==============================================================================
   *
   * @version v1.0: api.php 20/10/2017
   * @copyright Copyright (c) 2017 S4 dx
   * @author Osmar Betazzi Dordal
   * @ Robson de Almeida Fiatcoski
   *
   * ==============================================================================
   *
   */

	require_once("Rest.inc.php");
	require_once("database_configuration.php");

	class API extends REST{

		public function __construct(){
			parent::__construct();				// Init parent contructor
		}

		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */
		public function processApi($method){
           
			$func = strtolower(trim(str_replace("/","",$_REQUEST['action'])));
			if ((int)method_exists($this, $func) > 0){
				$this->$func($method);
			}else{
				$this->response('',404); // If the method not exist with in this class, response would be "Page not found".
			}
		}





        /**
         * Usage: App_Updater_String_Util::utf8_encode( $data );
         *
         * @param mixed $d
         * @return mixed
         * @see http://stackoverflow.com/questions/19361282/why-would-json-encode-returns-an-empty-string
         */
        public static function utf8_encode($d) {
            if (is_array($d)) {
                foreach ($d as $k => $v) {
                    $d[$k] = self::utf8_encode($v);
                }
            } elseif (is_object($d)) {
                foreach ($d as $k => $v) {
                    $d->$k = self::utf8_encode($v);
                }
            } elseif (is_scalar($d)) {
                $d = utf8_encode($d);
            }

            return $d;
        }
    

        /**
         *	Get User
         *  method : GET
         *  data   : json data
         *  Output : json data
         * For Get user you just need a valid key, dont't need to pay to get the user data
         */
        private function get_user($method){
            global $conn;

            $data = $this->convert_json_to_array($this->_request['data']);
            
            //echo $method;
            //print_r($data);
            if($method != "GET"){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }
        

            $key         = $data['key'];
            $id_usuario  = $data['id'];

            // Input validations
            if (empty($key)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($id_usuario)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "User ID not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }
            //check if the user id exist in the db
             
            
             $query = "SELECT id FROM usuario WHERE id = '".$id_usuario."'";
             //echo $query;
             $result = mysqli_query($conn, $query);
             if (mysqli_num_rows($result) == 0){
                $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                $id = $user_data["id"];

                $arr_res=array();
                $arr_res['error']  = array("msg" => "User not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 404);
            }

            // Key Value 
            
            // check if we have a RO key
            $query = "SELECT token FROM token WHERE token = '".$key."' and ArrivalDate>= now() - INTERVAL 1 HOUR;";
            echo $query;
            $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) == 0){

                 //if don't have a RO key check if there is a RW Key
                 $query = "SELECT token FROM usuario WHERE token = '".$key."';";
                 $result = mysqli_query($conn, $query);
                 if (mysqli_num_rows($result) == 0)
                 {
                    $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                    $token = $user_data["token"];

                    $arr_res=array();
                    $arr_res['error']  = array("msg" => "Expired  Key");
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 400);
                    
                 }
                    //Payment verification
                     $query = "SELECT stt from usuario where token ='".$key."'";
                    $result = mysqli_query($conn,$query);
                    $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                    $stt = $user_data["stt"];
                    //if the STT equals to 1 need to pay to access this data
                     if($stt == 1)
                    {
                        echo $stt;
                        $arr_res=array();
                        $arr_res['error']  = array("msg" => "Payment Required");
                        $arr_res['result'] = array('status' => "Failed");
                        $this->response($this->json($arr_res), 402);

                    }

                }
               
                   
            
            
            
            // Input validations
            if(!empty($key) && !empty($id_usuario)){

                $query = "SELECT id,                             
                                 cpf, 
                                 nome, 
                                 email, 
                                 dtnasc,                                  
                                 sexo, 
                                 stt, 
                                 logradouro, 
                                 numero, 
                                 complemento, 
                                 bairro, 
                                 municipio,
                                 estado,
                                 cep
                                  
                            FROM usuario 
                            WHERE id = ".$id_usuario.";";
                
                $result = mysqli_query($conn, $query);
                
                if(mysqli_num_rows($result) > 0){
                    $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                    $id = $user_data["id"];
        
                    $cpf = $user_data["cpf"];
                    $nome = $this->utf8_encode($user_data["nome"]);
                    $email = $user_data["email"];
                    $dtnasc = $user_data["dtnasc"];
                    $sexo = $user_data["sexo"];
                    $stt = $user_data["stt"];
                    $logradouro = $this->utf8_encode($user_data["logradouro"]);
                    $numero = $user_data["numero"];
                    $complemento = $this->utf8_encode($user_data["complemento"]);
                    $bairro = $this->utf8_encode($user_data["bairro"]);
                    $cep = $user_data["cep"];
                    

                    $error = NULL;
                    $str_array = array(
                        'status' => "Success" ,
                        'id' => $id,
                        'cpf' => $cpf,
                        'nome' => $nome,
                        'email' => $email,
                        'dtnasc' => $dtnasc,
                        'sexo' => $sexo,
                        'ativo' => $stt,
                        'logradouro' => $logradouro,
                        'numero' => $numero,
                        'complemento' => $complemento,
                        'bairro' => $bairro,
                        'cep' => $cep
                        );
                    $arr_res = array();
                    $arr_res['error']  = $error;
                    $arr_res['result'] = $str_array;
                    $this->response($this->json($arr_res), 200);
                } else {
                    $error = array("msg" => "No Content");
                    $arr_res = array();
                    $arr_res['error']  = $error;
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 204);
                }
            }

        }

        /**
         *	Add User
         *  method : POST
         *  data   : json data
         *  Output : json data
         *  Rule: 
         */

		private function add_user($method){
           
           
            global $conn;
             
            $data = $this->convert_json_to_array($this->_request['data']);

            if($method != "POST")
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }
            
            
            $key            = $data['key'];
            $cpf            = $data['cpf'];
            $nome           = $data['nome'];
            $email          = $data['email'];
            $senha          = $data['senha'];
            $dtnasc         = $data['dtnasc'];
            $stt            = $data['stt'];
            $sexo           = $data['sexo'];
            $logradouro     = $data['logradouro'];
            $numero         = $data['numero'];
            $complemento    = $data['complemento'];
            $bairro         = $data['bairro'];
            $municipio      = $data['municipio'];
            $estado         = $data['estado'];
            $cep            = $data['cep'];

            // Input validations
            
            
            if (empty($key)){
                echo "caiu aqui";
                echo $cpf;
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            if (empty($cpf)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "CPF value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            } else {

                $query = "SELECT id FROM usuario WHERE cpf = '".$cpf."'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0)
                {
                    $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                    $user_id = $user_data["id"];

                    $arr_res = array();
                    $arr_res['error']  = array("msg" => "User already exist in database (CPF). id = ".$user_id);
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 200);
                }

            }

			if (empty($nome)){
				$arr_res=array();
				$arr_res['error']  = array("msg" => "Name value not found");
				$arr_res['result'] = array('status' => "Failed");
				$this->response($this->json($arr_res), 400);
			}

			if (empty($email)){
				$arr_res=array();
				$arr_res['error']  = array("msg" => "Email value not found");
				$arr_res['result'] = array('status' => "Failed");
				$this->response($this->json($arr_res), 400);
			}

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $error = array("msg" => "Invalid Email address");
                $arr_res = array();
                $arr_res['error']  = $error;
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if ($email != ""){
                $query = "SELECT id FROM usuario WHERE email = '".$email."'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0){
                    $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                    $user_id = $user_data["id"];

                    $arr_res = array();
                    $arr_res['error']  = array("msg" => "User already exist in database (E-mail). id = ".$user_id);
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 200);
                }
            }

			if (empty($senha)){
				$arr_res=array();
				$arr_res['error']  = array("msg" => "Password value not found");
				$arr_res['result'] = array('status' => "Failed");
				$this->response($this->json($arr_res), 400);
			} elseif (strlen($senha) != 32) {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Password wrong size");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

			if (empty($dtnasc)){
				$arr_res=array();
				$arr_res['error']  = array("msg" => "Birthday Date value not found");
				$arr_res['result'] = array('status' => "Failed");
				$this->response($this->json($arr_res), 400);
			} else {
                date_default_timezone_set('America/Sao_Paulo');
                /*
                echo $dtnasc;
                $date = new DateTime(strtotime($dtnasc));
                echo $date;
                $dtnasc = $date->format('Y-m-d');
                */
                $dtnasc = date('Y-m-d', strtotime($dtnasc));
            }

			if (empty($stt)){
				$arr_res=array();
				$arr_res['error']  = array("msg" => "Situation Value not found");
				$arr_res['result'] = array('status' => "Failed");
				$this->response($this->json($arr_res), 400);
			}

            if (empty($sexo)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Sex value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($logradouro)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Address value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($numero)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Address Number value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($bairro)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Neighborhood value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($municipio)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "City value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($estado)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Estate value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($cep)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "ZIP Code value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

             // Key Value 
            
             $query = "SELECT token FROM usuario WHERE token = '".$key."'";
             $result = mysqli_query($conn, $query);
             
             if (mysqli_num_rows($result) == 0){
                 $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                 $token = $user_data["token"];

                 $arr_res=array();
                 $arr_res['error']  = array("msg" => "Invalid Key");
                 $arr_res['result'] = array('status' => "Failed");
                 $this->response($this->json($arr_res), 400);
             }
                
         
         
         //Payment verification
         $query = "SELECT stt from usuario where token ='".$key."'";
         $result = mysqli_query($conn,$query);
         $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
         $stt = $user_data["stt"];
         
         if($stt == 0)
         {
             echo $stt;
             $arr_res=array();
             $arr_res['error']  = array("msg" => "Payment Required");
             $arr_res['result'] = array('status' => "Failed");
             $this->response($this->json($arr_res), 402);

         }

            

			if (!empty($key)
				&& !empty($cpf)
				&& !empty($nome)
				&& !empty($email)
				&& !empty($senha)
				&& !empty($dtnasc)
				&& !empty($stt)
				&& !empty($sexo)
				&& !empty($logradouro)
				&& !empty($numero)
				&& !empty($complemento)
				&& !empty($bairro)
				&& !empty($municipio)
				&& !empty($estado)
				&& !empty($cep))
			{
                $token = bin2hex(openssl_random_pseudo_bytes(8));
				$insert_query = "INSERT INTO usuario (
                    cpf, 
                    nome, 
                    email, 
                    senha, 
                    dtnasc, 
                    stt, 
                    sexo, 
                    logradouro, 
                    numero, 
                    complemento, 
                    bairro, 
                    municipio, 
                    estado,
                    cep,
                    token)";

				$insert_query .= " values (
                    '".$cpf."',
                    '".$nome."',
                    '".$email."',
                    '".$senha."',
                    '".$dtnasc."',
                    '".$stt."',
                    '".$sexo."',
                    '".$logradouro."',
                    '".$numero."',
                    '".$complemento."',
                    '".$bairro."', 
                    '".$municipio."',
                    '".$estado."',
                    ".$cep.",
                    '".$token."');";
				//echo $insert_query;
                mysqli_query($conn, $insert_query);
                echo $insert_query;
               

				$query = "SELECT id FROM usuario WHERE cpf = '".$cpf."'";
				$result = mysqli_query($conn, $query);
				if (mysqli_num_rows($result) > 0){
					$user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
					$user_id = $user_data["id"];
				}
				//$user_id = mysqli_stmt_insert_id($conn);

				$error = NULL;
				$str_array = array('status' => "User was created success" ,'id_usuario' => $user_id, 'token'=> $token);
				$arr_res = array();
				$arr_res['error']  = $error;
				$arr_res['result'] = $str_array;
				$this->response($this->json($arr_res), 201);
			}

			$error = array("msg" => "Invalid input parameter");

			$arr_res = array();
			$arr_res['error']  = $error;
			$arr_res['result'] = array('status' => "Failed");
            $this->response($this->json($arr_res), 400);
            
        }/* Fim Add user */

        /**
         *	Update User
         *  method : POST
         *  data   : json data
         *  Output : json data
         */
        private function update_user($method){
            global $conn;

            $data = $this->convert_json_to_array($this->_request['data']);
            
            

            if($method != "PUT")
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            $key            = $data['key'];
            $cpf            = $data['cpf'];
            $id_usuario     = $data['usuario'];
            $nome           = $data['nome'];
            $email          = $data['email'];
            $dtnasc         = $data['dtnasc'];
            $stt            = $data['stt'];
            $sexo           = $data['sexo'];
            $logradouro     = $data['logradouro'];
            $numero         = $data['numero'];
            $complemento    = $data['complemento'];
            $bairro         = $data['bairro'];
            $municipio      = $data['municipio'];
            $estado         = $data['estado'];
            $cep            = $data['cep'];


            // Input validations
            
            if (empty($key)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($id_usuario)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "User ID not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            if (empty($cpf)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "CPF value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($nome)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Name value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($email)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Email value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $error = array("msg" => "Invalid Email address");
                $arr_res = array();
                $arr_res['error']  = $error;
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($dtnasc)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Birthday Date value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            } else {
                date_default_timezone_set('America/Sao_Paulo');
                $dtnasc = date('Y-m-d', strtotime($dtnasc));
            }

            if (empty($sexo)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Sex value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($logradouro)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Address value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($numero)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Address Number value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            if (empty($bairro)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Neighborhood value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($municipio)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "City value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($estado))
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "State value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($stt)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Situation value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($cep)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "ZIP Code value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

              // Key Value 
            
              $query = "SELECT token FROM usuario WHERE token = '".$key."'";
              $result = mysqli_query($conn, $query);
              
              if (mysqli_num_rows($result) == 0){
                  $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                  $token = $user_data["token"];

                  $arr_res=array();
                  $arr_res['error']  = array("msg" => "Invalid Key");
                  $arr_res['result'] = array('status' => "Failed");
                  $this->response($this->json($arr_res), 400);
              }
                 
          
          
                //Payment verification
                $query = "SELECT stt from usuario where token ='".$key."'";
                $result = mysqli_query($conn,$query);
                $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                $stt = $user_data["stt"];
                
                if($stt == 0)
                {
                    echo $stt;
                    $arr_res=array();
                    $arr_res['error']  = array("msg" => "Payment Required");
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 402);

                }
                //check if the user id exist in the db
             
            
             $query = "SELECT id FROM usuario WHERE id = '".$id_usuario."'";
             //echo $query;
             $result = mysqli_query($conn, $query);
             if (mysqli_num_rows($result) == 0){
                $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                $id = $user_data["id"];

                $arr_res=array();
                $arr_res['error']  = array("msg" => "User not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 404);
            }


            if (!empty($key) && !empty($cpf) && !empty($id_usuario) && !empty($nome) && !empty($email) && !empty($dtnasc) && !empty($stt) && !empty($sexo) && !empty($logradouro) &&  !empty($numero) && !empty($bairro) && !empty($municipio) && !empty($estado) && !empty($cep))
            {

                $update_query = "UPDATE usuario SET 
                                  cpf = '".$cpf."', 
                                  id = '".$id_usuario."', 
                                  nome = '".$nome."', 
                                  email = '".$email."', 
                                  dtnasc = '".$dtnasc."', 
                                  stt = '".$stt."', 
                                  sexo = '".$sexo."', 
                                  logradouro = '".$logradouro."', 
                                  numero = '".$numero."', 
                                  bairro = '".$bairro."', 
                                  municipio = '".$municipio."', 
                                  estado = '".$estado."', 
                                  cep = '".$cep."', 
                                  complemento = '".$complemento."'  
                                  WHERE id = ".$id_usuario.";";
                echo $update_query;
                mysqli_query($conn, $update_query);

                $error = NULL;
                $str_array = array('status' => "Success" ,'id_usuario' => $id_usuario);
                $arr_res = array();
                $arr_res['error']  = $error;
                $arr_res['result'] = $str_array;
                $this->response($this->json($arr_res), 200);
            }

            // If invalid inputs "Bad Request" status message and reason
            $error = array("msg" => "Invalid input parameter");

            $arr_res = array();
            $arr_res['error']  = $error;
            $arr_res['result'] = array('status' => "Failed");
            $this->response($this->json($arr_res), 400);
        }/** Fim Update user */

        /**
         *	Login User
         *  method : GET
         *  data   : json data
         *  Output : json data
         * @param: key, email, senha[md5]
         */
        private function login_user($method)
        {
            global $conn;

            $data = $this->convert_json_to_array($this->_request['data']);
            

            if ($method != "GET") {
                $arr_res = array();
                $arr_res['error'] = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            $key         = $data['key'];
            $email       = $data['email'];
            $password    = $data['password'];


            if(count($data) > 3){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Number of parameters incorrect");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            // Input validations
            if (empty($key))
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($email))
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "E-mail value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($password))
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Password value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            } elseif (strlen($password) != 32) {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Password wrong size");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

             // Key Value 
            
             $query = "SELECT token,email FROM usuario WHERE token = '".$key."'";
             $result = mysqli_query($conn, $query);
             
             if (mysqli_num_rows($result) == 0){
                 $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                 $token = $user_data["token"];

                 $arr_res=array();
                 $arr_res['error']  = array("msg" => "Invalid Key");
                 $arr_res['result'] = array('status' => "Failed");
                 $this->response($this->json($arr_res), 400);
             }
             //Check if the key is from the same user whe try to login
             $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
             $token = $user_data["token"];
             $dbemail = $user_data["email"];
             if($dbemail !=$email ){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Unauthorized");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 401);
             }
                
         
         
                //Payment verification
                $query = "SELECT stt from usuario where token ='".$key."'";
                $result = mysqli_query($conn,$query);
                $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                $stt = $user_data["stt"];
                
                if($stt == 1)
                {
                    echo $stt;
                    $arr_res=array();
                    $arr_res['error']  = array("msg" => "Payment Required");
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 402);

                }
            // Input validations
            if(!empty($key) && !empty($email) && !empty($password))
            {
                if(filter_var($email, FILTER_VALIDATE_EMAIL))
                {

                    $query = "SELECT id FROM usuario WHERE email = '$email' AND senha = '".$password."'";
                    $result = mysqli_query($conn, $query);
                    if(mysqli_num_rows($result) > 0)
                    {
                        $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                        $user_id = $user_data["id"];

                        // User found
                        $error = NULL;
                        $str_array = array('status' => "Success" ,'id_usuario' => $user_id);
                        $arr_res = array();
                        $arr_res['error']  = $error;
                        $arr_res['result'] = $str_array;
                        $this->response($this->json($arr_res), 200);
                    } else {
                        echo "User not found";
                        $error = array("status" => "No Content");
                        echo "error";
                        $arr_res = array();
                        $arr_res['error']  = $error;
                        $arr_res['result'] = array('status' => "Failed");
                        $this->response($this->json($arr_res), 204);
                    }
                }
            }

            // If invalid inputs "Bad Request" status message and reason
            $error = array("msg" => "Invalid Email address or Password");
            $arr_res = array();
            $arr_res['error']  = $error;
            $arr_res['result'] = array('status' => "Failed");
            $this->response($this->json($arr_res), 400);

        }

        /**
         *	Update User
         *  method : DELETE
         *  data   : json data
         *  Output : json data
         */
        private function delete_user($method)
        {
            global $conn;

            $data = $this->convert_json_to_array($this->_request['data']);
           

            if ($method != "DELETE") {
                $arr_res = array();
                $arr_res['error'] = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }
            //exit();
            $key         = $data['key'];
            $id_usuario  = $data['id'];

            // Input validations
            if (empty($key)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($id_usuario)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "User ID not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

              // Key Value 
            
              $query = "SELECT token FROM usuario WHERE token = '".$key."'";
              $result = mysqli_query($conn, $query);
              
              if (mysqli_num_rows($result) == 0){
                  $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                  $token = $user_data["token"];

                  $arr_res=array();
                  $arr_res['error']  = array("msg" => "Invalid Key");
                  $arr_res['result'] = array('status' => "Failed");
                  $this->response($this->json($arr_res), 400);
              }
                 
          
                
                //Payment verification
                $query = "SELECT stt from usuario where token ='".$key."'";
                $result = mysqli_query($conn,$query);
                $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                $stt = $user_data["stt"];
                
                if($stt == 0)
                {
                    echo $stt;
                    $arr_res=array();
                    $arr_res['error']  = array("msg" => "Payment Required");
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 402);

                }
            
             // Input validations
             if(!empty($key) && !empty($id_usuario)){

                $query = "delete 
                            FROM usuario 
                            WHERE id = ".$id_usuario.";";
                
                $result = mysqli_query($conn, $query);
                $error = NULL;
				$str_array = array('status' => "Success deleted" ,'id_usuario' => $user_id);
				$arr_res = array();
				$arr_res['error']  = $error;
				$arr_res['result'] = $str_array;
				$this->response($this->json($arr_res), 200);
                
               
                } else {
                    $error = array("msg" => "No Content");
                    $arr_res = array();
                    $arr_res['error']  = $error;
                    $arr_res['result'] = array('status' => "Failed");
                    $this->response($this->json($arr_res), 204);
                }
            
            }
       
    
        //Fim delete user

        /**
         *	Update Disable and Enable User
         *  method : PUT
         *  data   : json data
         *  Output : json data
         */
        private function disable_enable_user($method){
            global $conn;

            $data = $this->convert_json_to_array($this->_request['data']);
            

            if($method != "PUT")
            {
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Incorrect Method");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            $key            = $data['key'];
            $id_usuario     = $data['usuario'];
            $stt            = $data['stt'];



            // Input validations
            if (empty($key)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Key value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

            if (empty($id_usuario)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "User ID not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }


            

            if (empty($stt)){
                $arr_res=array();
                $arr_res['error']  = array("msg" => "Situation value not found");
                $arr_res['result'] = array('status' => "Failed");
                $this->response($this->json($arr_res), 400);
            }

           
              // Key Value 
            
              $query = "SELECT token FROM usuario WHERE token = '".$key."'";
              $result = mysqli_query($conn, $query);
              
              if (mysqli_num_rows($result) == 0){
                  $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
                  $token = $user_data["token"];

                  $arr_res=array();
                  $arr_res['error']  = array("msg" => "Invalid Key");
                  $arr_res['result'] = array('status' => "Failed");
                  $this->response($this->json($arr_res), 400);
              }
                 
          
          
          //Payment verification
          $query = "SELECT stt from usuario where token ='".$key."'";
          $result = mysqli_query($conn,$query);
          $user_data = mysqli_fetch_array($result,MYSQLI_ASSOC) ;
          $stt1 = $user_data["stt"];
          
          if($stt1 == 1)
          {
              echo $stt1;
              $arr_res=array();
              $arr_res['error']  = array("msg" => "Payment Required");
              $arr_res['result'] = array('status' => "Failed");
              $this->response($this->json($arr_res), 402);

          }

            //if the user not pay disable user and change the token
            //if sst = 1 disable user and set the token to null
            //if stt = 2 enable user and set a new token
            if (!empty($key) && !empty($id_usuario) && !empty($stt)){
            if($stt == 1)
            {

                $update_query = "UPDATE usuario SET 
                                  stt = '".$stt."'
                                  
                                  WHERE id = ".$id_usuario.";";
                echo $update_query;
                mysqli_query($conn, $update_query);

                $error = NULL;
                $str_array = array('status' => "User Disable" ,'id_usuario' => $id_usuario);
                $arr_res = array();
                $arr_res['error']  = $error;
                $arr_res['result'] = $str_array;
                $this->response($this->json($arr_res), 200);
            }
            //if user pay enable user and create a new token
            else{
                //Generate a new token
                $token = bin2hex(openssl_random_pseudo_bytes(8));
                echo $token;
                $update_query = "UPDATE usuario SET 
                                  stt = '".$stt."',
                                  token = '".$token."'
                                  WHERE id = ".$id_usuario.";";
                echo $update_query;
                mysqli_query($conn, $update_query);

                $error = NULL;
                $str_array = array('status' => "User enable and generate a new token",'id_usuario' => $id_usuario,'new token'=> $token);
                $arr_res = array();
                $arr_res['error']  = $error;
                $arr_res['result'] = $str_array;
                $this->response($this->json($arr_res), 200);
            }
        }   
            // If invalid inputs "Bad Request" status message and reason
            $error = array("msg" => "Invalid input parameter");

            $arr_res = array();
            $arr_res['error']  = $error;
            $arr_res['result'] = array('status' => "Failed");
            $this->response($this->json($arr_res), 400);
        }/** Fim Update user */

		/*
		 *	Decode JSON into array
		*/
		private function convert_json_to_array($json)
		{
			return json_decode($json,true);
		}

		/*
		 *	Encode array into JSON
		*/
		private function json($data)
		{
			if (is_array($data)) {
				return json_encode($data);
			} else {
			    return NULL;
			}
		}
	}

	// Initiate Library
	$api = new API;
    $method = $api->get_request_method();
	$api->processApi($method);

?>
