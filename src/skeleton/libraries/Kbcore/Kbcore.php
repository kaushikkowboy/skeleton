<?php
/**
 * CodeIgniter Skeleton
 *
 * A ready-to-use CodeIgniter skeleton  with tons of new features
 * and a whole new concept of hooks (actions and filters) as well
 * as a ready-to-use and application-free theme and plugins system.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package 	CodeIgniter
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @copyright	Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 * @license 	http://opensource.org/licenses/MIT	MIT License
 * @link 		https://github.com/bkader
 * @since 		Version 1.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Some of our drivers need to respect this interface's
 * structure. So we are importing it here.
 */
require_once('CRUD_interface.php');

/**
 * Main application library.
 *
 * @package 	CodeIgniter
 * @category 	Libraries
 * @author 	Kader Bouyakoub <bkader@mail.com>
 * @link 	https://github.com/bkader
 */
class Kbcore extends CI_Driver_Library
{
	/**
	 * Instance of CI object.
	 * @var object
	 */
	public $ci;

	/**
	 * Array of valid drivers.
	 * @var array
	 */
	public $valid_drivers;

	/**
	 * Class constructor
	 * @return 	void
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		// Fill valid drivers.
		$this->valid_drivers = array(
			'activities',
			'entities',
			'groups',
			'menus',
			'media',
			'metadata',
			'options',
			'objects',
			'plugins',
			'relations',
			'users',
			'variables',
			// 'theme',
		);

		// Here we load all what we need.
		$this->ci->load->database();
		$this->ci->load->library('session');

		// Let's assign options from database to CodeIgniter config.
		$this->ci->load->config('defaults');

		/**
		 * Here we are making an instance of this driver global
		 * so that themes, plugins or others can use it.
		 */
		global $KB, $DB;
		$KB = new stdClass();
		foreach ($this->valid_drivers as $driver)
		{
			$this->{$driver}->initialize();
			$KB->{$driver} = $this->{$driver};
		}
		$KB->ci =& $this->ci;
		$DB =& $this->ci->db;

		// Store language in session.
		if ( ! $this->ci->session->language)
		{
			$this->_set_language();
		}

		// Make sure to load the URL helper.
		$this->ci->load->helper('url');

		/**
		 * Loading the language helper is now useless because the
		 * lang() function was moved to KB_Lang.php file so it is
		 * available even if we don't load the helpe.
		 */
		// $this->ci->load->helper('language');

		// Loading theme library.
		$this->ci->load->library('theme');

		// Make current language available to themes.
		$this->_languages_list();

		// Load main language file.
		$this->ci->load->language('main');

		// Attempt to authenticate the current user.
		// $this->auth->authenticate();
		$this->ci->load->library('users/auth', array('kbcore' => $this));

		// Initialize plugins if plugins system is enabled.
		$this->plugins->load_plugins();

		log_message('info', 'Kbcore Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Quick action to add meta tags to given page.
	 * @access 	public
	 * @param 	mixed 	$object 	the page or course. (object or array)
	 * @author 	Kader Bouyakoub
	 * @version 1.0
	 * @return 	void
	 */
	public function set_meta($object = null)
	{
		// Add favicon.
		$this->ci->theme->add_meta('icon', base_url('favicon.ico'), 'rel', 'type="image/x-icon"');

		// Default meta tags that will be overridden later.

		// Site name and default title.
		if ($this->ci->config->item('site_name'))
		{
			$this->ci->theme->set_title($this->ci->config->item('site_name'));
			$this->ci->theme->set('site_name', $this->ci->config->item('site_name'));
			$this->ci->theme->add_meta('application-name', $this->ci->config->item('site_name'));
			$this->ci->theme->add_meta('title', $this->ci->config->item('site_name'));
		}

		// Site description.
		if ($this->ci->config->item('site_description'))
		{
			$this->ci->theme->add_meta('description', $this->ci->config->item('site_description'));
		}

		// Site keywords.
		if ($this->ci->config->item('site_keywords'))
		{
			$this->ci->theme->add_meta('keywords', $this->ci->config->item('site_keywords'));
		}

		// Add site's author if found.
		if ($this->ci->config->item('site_author'))
		{
			$this->ci->theme->add_meta('author', $this->ci->config->item('site_author'));
		}

		// Add google site verification IF found.
		if ($this->ci->config->item('google_site_verification'))
		{
			$this->ci->theme->add_meta(
				'google-site-verification',
				$this->ci->config->item('google_site_verification')
			);
		}

		// Add Google Anaytilcs IF found!
		if ($this->ci->config->item('google_analytics_id')
			&& $this->ci->config->item('google_analytics_id') !== 'UA-XXXXX-Y')
		{
			$this->ci->theme->add_meta(
				'google-analytics',
				$this->ci->config->item('google_analytics_id')
			);
		}

		// Add canonical tag.
		$this->ci->theme->add_meta('canonical', current_url(), 'rel');

		// Is $object provided?
		if ($object !== null)
		{
			// Is it an object?
			if (is_object($object))
			{
				$this->ci->theme->add_meta('title', $object->name);
				$this->ci->theme->add_meta('og:title', $object->name);

				if ( ! empty($object->description))
				{
					$this->ci->theme->add_meta('description', $object->description);
					$this->ci->theme->add_meta('og:description', $object->description);
				}
			}
			// Is it an array?
			elseif (is_array($object))
			{
				$this->ci->theme->add_meta('title', $object['name']);
				$this->ci->theme->add_meta('og:title', $object['name']);

				if ( ! empty($object['description']))
				{
					$this->ci->theme->add_meta('description', $object['description']);
					$this->ci->theme->add_meta('og:description', $object['description']);
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Quick method to send emails.
	 * @access 	public
	 * @param 	string 	$to 		the whom send the email.
	 * @param 	string 	$subject 	the email's subject?
	 * @param 	string 	$messages 	the email's body.
	 * @param 	string 	$cc 		carbon copy.
	 * @param 	string 	$bcc 		blind carbon copy.
	 * @author 	Kader Bouyakoub
	 * @version 1.0
	 * @return 	bool 	true if the email is sent.
	 */
	public function send_email($to, $subject, $message, $cc = null, $bcc = null)
	{
		$this->ci->load->library('email');

		// Start by setting up the email config.
		$mail_protocol = $this->ci->config->item('mail_protocol');
		$config['mail_protocol'] = $mail_protocol;

		/*
			Now we set the rest of the config parameters
			depending on the mail protocol.
		 */
		switch ($mail_protocol)
		{
			// The old fashion way?
			case 'mail':
				// Nothing to add.
				break;

			// Using SMTP?
			case 'smtp':
				$config['smtp_host']   = $this->ci->config->item('smtp_host');
				$config['smtp_user']   = $this->ci->config->item('smtp_user');
				$config['smtp_pass']   = $this->ci->config->item('smtp_pass');
				$config['smtp_port']   = $this->ci->config->item('smtp_port');
				$config['smtp_crypto'] = $this->ci->config->item('smtp_crypto');
				($config['smtp_crypto'] == 'none') && $config['smtp_crypto'] = '';
				break;

			// Using sendmain?
			case 'sendmail':
				// The server path to Sendmail. Default: '/usr/sbin/sendmail'
				$config['mailpath'] = $this->ci->config->item('sendmail_path');
				break;

			// Default (which is mail).
			default:
				/*
					$mail_protocol ended up being something
					other than the 3 we check for, so we override
					whatever it was and go with 'mail'
				 */
				$config['protocol'] = 'mail';
				break;
		}

		/*
			the rest of the config items we don't need to
			worry about which protocol the site is using...
		 */
		$config['charset']   = 'utf8';
		$config['wordwrap']  = true;
		$config['useragent'] = $this->ci->config->item('site_name');
		$config['mailtype']  = 'html';

		// Let's now initialize email library.
		$this->ci->email->initialize($config);

		// The from is obviously from the database.
		$this->ci->email->from(
			$this->ci->config->item('server_email'),
			$this->ci->config->item('site_name')
		);

		// To whow send this email.
		$this->ci->email->to($to);

		// A carbon copy is set?
		if ( ! empty($cc))
		{
			$this->ci->email->cc($cc);
		}

		// A blind carbon copy is set?
		if ( ! empty($bcc))
		{
			$this->ci->email->bcc($bcc);
		}

		// Prepare the email subject.
		$this->ci->email->subject($subject);

		// Set the email message.
		$this->ci->email->message($message);

		// And here we go! Send it.
		if ( ! $this->ci->email->send())
		{
			log_message('error', 'Emails are not being sent!');
			$this->ci->email->print_debugger();
		}

		return true;
	}

	// ------------------------------------------------------------------------

	/**
	 * Make sure to store language in session.
	 * @access 	private
	 * @param 	none
	 * @return 	void
	 */
	private function _set_language()
	{
		// Hold the default language.
		$default = $this->ci->config->item('language');

		// Site available languages.
		$site_languages = $this->ci->config->item('languages');

		// All languages to details to search in.
		$languages = $this->ci->lang->languages();

		// Attempt to detect user's language.
		$code = substr($this->ci->input->server('HTTP_ACCEPT_LANGUAGE', true), 0, 2);

		foreach ($languages as $folder => $details)
		{
			if ($details['code'] === $code && in_array($folder, $site_languages))
			{
				$default = $folder;
				break;
			}
		}

		// Now we setup the session data.
		$this->ci->session->set_userdata('language', $default);
	}

	// ------------------------------------------------------------------------

	/**
	 * Pass available site languages to theme views in order to use them
	 * for language switch.
	 * @access 	private
	 * @param 	void
	 * @return 	void
	 */
	private function _languages_list()
	{
		// Get the list of all languages details first.
		$languages = $this->ci->lang->languages();

		// Make sure current language available to views.
		$this->ci->theme->set(
			'current_language',
			$languages[$this->ci->session->language],
			true
		);

		// Site languages stored in configuration.
		$config_languages = $this->ci->config->item('languages');

		// Add our available languages to views.
		$langs = array();

		if (count($config_languages) > 0)
		{
			foreach ($languages as $folder => $details)
			{
				if (in_array($folder, $config_languages) && $folder !== $this->ci->session->language)
				{
					$langs[$folder] = $details;
				}
			}
		}
		$this->ci->theme->set('site_languages', $langs, true);
	}

	// ------------------------------------------------------------------------

	/**
	 * Database WHERE clause generator.
	 *
	 * @since 	1.3.0
	 *
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @param 	int 	$limit
	 * @param 	int 	$offset
	 * @return 	object 	it returns the DB object so that the method can be chainable.
	 */
	public function where($field = null, $match = null, $limit = 0, $offset = 0)
	{
		if ($field !== null)
		{
			// Format things first.
			if (is_array($field))
			{
				$limit  = $match;
				$offset = $limit;
			}
			else
			{
				$field = array($field => $match);
			}

			// Let's generate the WHERE clause.
			foreach ($field as $key => $val)
			{
				// We make sure to ignore empty key.
				if (empty($key) OR is_int($key))
				{
					continue;
				}

				// The default method to call.
				$method = 'where';

				// In case $val is an array.
				if (is_array($val))
				{
					// The default method to call is "where_in".
					$method = 'where_in';

					// Should we use the "or_where_not_in"?
					if (strpos($key, 'or:!') === 0)
					{
						$method = 'or_where_not_in';
						$key    = str_replace('or:!', '', $key);
					}
					// Should we use the "or_where_in"?
					elseif (strpos($key, 'or:') === 0)
					{
						$method = 'or_where_in';
						$key    = str_replace('or:', '', $key);
					}
					// Should we use the "where_not_in"?
					elseif (strpos($key, '!') === 0)
					{
						$method = 'where_not_in';
						$key    = str_replace('!', '', $key);
					}
				}

				$this->ci->db->{$method}($key, $val);
			}
		}

		if ($limit > 0)
		{
			$this->ci->db->limit($limit, $offset);
		}

		return $this->ci->db;
	}

	// ------------------------------------------------------------------------

	/**
	 * Database LIKE clause generator.
	 *
	 * @since 	1.3.0
	 * @since 	1.3.2 	The metadata column "key" was renamed back to "name".
	 *
	 * @param 	mixed 	$field
	 * @param 	mixed 	$match
	 * @param 	int 	$limit
	 * @param 	int 	$offset
	 * @param 	string 	$type 	The type of search: users, groups, objects OR null.
	 * @return 	object 	it returns the DB object so that the method can be chainable.
	 */
	public function find($field, $match = null, $limit = 0, $offset = 0, $type = null)
	{
		// Format things first.
		if (is_array($field))
		{
			$limit  = $match;
			$offset = $limit;
			$type   = $offset;
		}
		else
		{
			$field = array($field => $match);
		}

		/**
		 * The search is triggered depending of what we are looking for.
		 * This is useful because sometimes we may want to retrieve entities
		 * by their metadata. Otherwise, we generate a default LIKE clause.
		 */
		switch ($type)
		{
			// In case of looking for an entity.
			case 'users':
			case 'groups':
			case 'objects':

				// We make sure to join the required table.
				$this->ci->load->helper('inflector');
				$this->ci->db
					// We select only main tables fields to avoid joining metadata.
					->select("entities.*, {$type}.*")
					->distinct()
					->where('entities.type', singular($type))
					->join($type, "{$type}.guid = entities.id");

				// The following anchoris  used to avoid multiple join.
				$metadata_joint = true;

				// Generate the query.
				$count = 1;
				foreach ($field as $key => $val)
				{
					/**
					 * If we are searching by a field that exists in one of the main
					 * tables: entities, users, groups or objects.
					 */
					if (in_array($key, $this->{$type}->fields()) 
						OR in_array($key, $this->entities->fields()))
					{
						// Make sure not to search in metadata.
						$metadata_joint = false;

						if ( ! is_array($val))
						{
							$method = ($count == 1) ? 'like' : 'or_like';
							if (strpos($key, '!') === 0)
							{
								$method = ($count == 1) ? 'not_like' : 'or_not_like';
								$key = str_replace('!', '', $key);
							}

							$this->ci->db->{$method}($key, $val);
						}
						else
						{
							foreach ($val as $_val)
							{
								$method = 'like';
								if (strpos($key, '!') === 0)
								{
									$method = 'not_like';
									$key = str_replace('!', '', $key);
								}

								$this->ci->db->{$method}($key, $val);
							}
						}

						$count++;
					}
					// Otherwise, we search by metadata.
					else
					{
						// Join metadata table?
						if ($metadata_joint === true)
						{
							$this->ci->db->join('metadata', 'metadata.guid = entities.id');

							// Stop multiple joins.
							$metadata_joint = false;
						}
						
						if ( ! is_array($val))
						{
							$method = ($count == 1) ? 'like' : 'or_like';
							if (strpos($key, '!') === 0)
							{
								$method = ($count == 1) ? 'not_like' : 'or_not_like';
								$key = str_replace('!', '', $key);
							}

							$this->ci->db->where('metadata.name', $key);
							$this->ci->db->{$method}('metadata.value', $val);
						}
						else
						{
							foreach ($val as $_val)
							{
								$method = 'like';
								if (strpos($key, '!') === 0)
								{
									$method = 'not_like';
									$key = str_replace('!', '', $key);
								}

								$this->ci->db->where('metadata.name', $key);
								$this->ci->db->{$method}('metadata.value', $val);
							}
						}

						$count++;
					}
				}

				break;	// End of case 'users', 'groups', 'objects'.
			
			// Generating default LIKE clause.
			default:

				// Let's now generate the query.
				$count = 1;
				foreach ($field as $key => $val)
				{
					if ( ! is_array($val))
					{
						$method = ($count == 1) ? 'like' : 'or_like';
						if (strpos($key, '!') === 0)
						{
							$method = ($count == 1) ? 'not_like' : 'or_not_like';
							$key = str_replace('!', '', $key);
						}

						$this->ci->db->{$method}($key, $val);
					}
					else
					{
						foreach ($val as $_val)
						{
							$method = 'like';
							if (strpos($key, '!') === 0)
							{
								$method = 'not_like';
								$key = str_replace('!', '', $key);
							}

							$this->ci->db->{$method}($key, $val);
						}
					}

					$count++;
				}

				break;	// End of "default".
		}

		// Did we provide a limit?
		if ($limit > 0)
		{
			$this->ci->db->limit($limit, $offset);
		}

		// Return this so the method can be chainable.
		return $this->ci->db;
	}

}
