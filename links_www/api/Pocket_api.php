<?php
/**
 * Pocket API class
 *
 * Simple implementation of Pocket API.
 *
 * http://getpocket.com/developer/
 *
 * @author		Viliam Schrojf <viliam.schrojf@gmail.com>
 * @link		http://getpocket.com/
 * @since		Version 0.9
 */


/**
 * Pocket API class
 *
 * Handle authentication and adding to your pocket account.
 * Now it only support adding but in new release it will
 * support all pocket api features (add, modify, retrieve).
 *
 * @link 	http://getpocket.com/developer/
 */
class Pocket_api
{
	/**
	 * The consumer key for your application
	 *
	 * @link 	http://getpocket.com/developer/apps/new
	 * @access 	public
	 * @var		string
	 */
	public $consumer_key = "YOUR_KEY";

	/**
	 * Ridirect URI
	 *
	 * Ridirection URI which will be called after
	 * user accept or reject your application in his
	 * pocket account.
	 *
	 * @access 	public
	 * @var		string
	 */
	public $redirect_uri = "http://localhost/test.php/2-step";

	/**
	 * Constructor - Sets Pocket API class Preferences
	 *
	 * The constructor can be passed an config values
	 *
	 * @param	string	$consumer_key = NULL
	 * @param	string	$redirect_uri = NULL
	 * @return	bool
	 */
	public function __construct($consumer_key = NULL, $redirect_uri = NULL)
	{

		if ($consumer_key !== NULL)
			$this->consumer_key = $consumer_key;

		if ($redirect_uri !== NULL)
			$this->redirect_uri = $redirect_uri;
	}

	/**
	 * The function will check if CURL function exist
	 *
	 * @return	bool
	 */
	public function curl_check()
	{
	  if( !function_exists("curl_ianit") &&
	      !function_exists("curl_setopt") &&
	      !function_exists("curl_exec") &&
	      !function_exists("curl_close") ) return FALSE;
	  else return TRUE;
	}

	/**
	 * Return status code from HTTP response headers
	 *
	 * @param	string	$response
	 * @param	string	$code
	 * @return	string or int -1 if no match found
	 */
	private function extract_code($response, $code)
	{
		$pattern = "/{$code}(.*)\s/";
		if (preg_match($pattern, $response, $matches) == 0)
		{
			return -1;
		}

		return $matches[1];
	}

	/**
	 * Send data to server with curl
	 * Return FALSE if curl fail or return array with
	 * x-code 0 if pocket response is OK or
	 * x-code 1 with error code and message
	 *
	 * @param	string		$url
	 * @param	string_json	$data
	 * @param	bool		$ssl = TRUE
	 * @return	array or boll FALSE
	 */
	private function post($url, $data, $ssl = TRUE)
	{
		$ch = curl_init();

		// todo: http://www.lornajane.net/posts/2011/posting-json-data-with-php-curl
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($ssl == TRUE)
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF8', 'X-Accept: application/json'));
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$response = curl_exec($ch);

		@curl_close($ch);

		if ($response === FALSE)
		{
			return FALSE;
		}

		//if ($this->extract_code($response,'Status: ') != "200 OK")
		if ( preg_match("/200 OK/", $response) == 0 )
		{
			return array(
					'x-error' => 1,
					'x-error-message' => $this->extract_code($response, "X-Error: "),
					'x-error-code' => $this->extract_code($response, "X-Error-Code: "),
					'status' => $this->extract_code($response,'Status: ')
					);
		}

		$json = preg_split('/\r\n\r\n/', $response);
		$answer = json_decode($json[1], TRUE);

		$answer['x-error'] = 0;
		return $answer;
	}

	/**
	 * Obtain a request token from Pocket server
	 * 1. step of auth see more info on
	 * http://getpocket.com/developer/docs/authentication
	 *
	 * Return answer from post function with x-error code
	 * for more info see pocket documentation or table below
	 *
	 * @return	array or boll FALSE
	 */
	public function obtain_request_token()
	{
		$url = "https://getpocket.com/v3/oauth/request";
		$data = json_encode(array(
			'consumer_key' => $this->consumer_key,
			'redirect_uri' => 'null' // this is not important for web application * read getPocket Documentation
				));

		/*
			http://getpocket.com/developer/docs/authentication

			Array()
			x-error	|	status	x-error-code	X-Error
			------- |   ------  ------------    ---------------------
			0		|	200		-				-
			1		|	400		138				Missing consumer key.
			1		|	400		140				Missing redirect url.
			1		|	403		152				Invalid consumer key.
			1		|	50X		199				Pocket server issue.
			* return FALSE if curl fail
		*/
		return $this->post($url,$data);

	}

	/**
	 * Generate autorization uri
	 * after autorization is user redirected
	 * to specified uri
	 *
	 * @param	string
	 * @return	string
	 */
	function user_authorization_link($request_token)
	{
		return "https://getpocket.com/auth/authorize?request_token=".$request_token."&redirect_uri=".$this->redirect_uri;
	}

	/**
	 * Obtain a access token
	 *
	 * The final step to authorize after user accept or reject
	 * autorization link is returned access token which is used
	 * with add, modify or retreive function
	 *
	 * x-error code table below
	 *
	 * @param 	string
	 * @return	array or boll FALSE
	 */
	function obtain_access_token($request_token)
	{
		$url = "https://getpocket.com/v3/oauth/authorize";
		$data = json_encode(array(
			'consumer_key' => $this->consumer_key,
			'code' => $request_token
				));

		/*
			http://getpocket.com/developer/docs/authentication

			Array()
			x-error	|	status	x-error-code	X-Error
			------- |   ------  ------------    ---------------------
			0		|	200		-				-
			1		|	400		138				Missing consumer key.
			1		|	403		152				Invalid consumer key.
			1		|	400		181				Invalid redirect uri.
			1		|	400		182				Missing code.
			1		|	400		185				Code not found.
			1		|	403		158				User rejected code.
			1		|	403		159				Already used code.
			1		|	50X		199				Pocket server issue.
			* return FALSE if curl fail
		*/

		return $this->post($url,$data);
	}

	/**

	*/
	function Add($access_token, $link)
	{
		$url = "https://getpocket.com/v3/add";
		$data = json_encode(array(
			'url' => $link,
			'consumer_key' => $this->consumer_key,
			'access_token' => $access_token
				));

		return $this->post($url,$data);
	}

}

/* End of file Pocket_api.php */