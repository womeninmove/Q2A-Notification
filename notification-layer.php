<?php
	require_once QA_INCLUDE_DIR.'qa-db.php';
	require_once QA_INCLUDE_DIR.'qa-db-selects.php';
	require_once QA_INCLUDE_DIR.'qa-app-format.php';
	require_once QA_INCLUDE_DIR.'qa-app-q-list.php';
	
	if(qa_opt("QA_NOTIFICATION_HAS_BEEN_SET_UP")!="true"){
					
		qa_db_query_sub("ALTER TABLE ^userevents ADD COLUMN lastview DATETIME");
		qa_db_query_sub("UPDATE ^userevents SET lastview = NOW() WHERE 1");
		
		qa_opt("QA_NOTIFICATION_HAS_BEEN_SET_UP", "true");
	}
	
	class qa_html_theme_layer extends qa_html_theme_base
	{
		/*
		 * For performance reasons, it's recommended you add the contents
		 * of notification.js to qa-content/qa-page.js and comment out this
		 * function
		 */
		function head_script(){
			$this->content['script'][]='<SCRIPT SRC="'.
				qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'notification.js').
				'" TYPE="text/javascript"></SCRIPT>';
				
			qa_html_theme_base::head_script();
		}
		
		/*
		 * For performance reasons, it's recommended you add the contents
		 * of notification.css to your theme's stylesheet and comment out this
		 * function
		 */
		function head_css(){
			qa_html_theme_base::head_css();
			$this->output('<link rel="stylesheet" href="'.qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'notification.css').'"/>');
			
			$this->content['style'][]='<link rel="stylesheet" href="'.
				qa_html(QA_HTML_THEME_LAYER_URLTOROOT.'notification.css').
				'" />';
				
			qa_html_theme_base::head_css();
		}
		
		
		var $urltoroot, $directory;

		function load_module($directory, $urltoroot)
		{
			$this->urltoroot = $urltoroot;
			$this->directory = $directory;
			
				
		}
		
		function logged_in(){
			if(qa_get_logged_in_userid()){
				$count = $this->notification_count();
				$notifyclass = "qa-notification-none";
				if($count>0){
					$notifyclass = "qa-notification-new";
				}
				
				$this->output('
				<a href="'.qa_path_html('updates').'"><span class="qa-notification-counter '.$notifyclass.'">'.$count.'</span></a>
				');
			}
			qa_html_theme_base::logged_in();
		}
		
		function notification_count(){
			$questions=qa_db_select_with_pending(
				qa_db_user_updates_selectspec(qa_get_logged_in_userid(), true, true)
			);

			if($this->template=="question"){
				$qnum = qa_request_part("0");
				qa_db_query_sub("UPDATE ^userevents SET lastview = NOW() WHERE questionid = $qnum AND userid = ".qa_get_logged_in_userid());
			}
			
			
			$questions = qa_db_read_all_assoc(qa_db_query_sub("SELECT DISTINCT questionid FROM ^userevents WHERE lastview < updated OR lastview IS NULL"));
			
			return count($questions);
		}
		
		
	}
