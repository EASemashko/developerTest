<?php

	define("RSS_URL", "https://lenta.ru/rss");
	define("NEWS_COUNT", 5);
	libxml_use_internal_errors(true);
	error_reporting(E_ERROR);

	class NewsPost 	{
		public $link;
		public $title;
		public $text;
	}

	class NewsFeed
	{
		private const ERROR_NOT_NON_NEGATIVE = "Second parameter should be 0 or more";
		public $posts = array();

		public function __construct(string $fileOrUrl, int $cnt = 0)
		{
			//This can be affected by a PHP bug #62577 (https://bugs.php.net/bug.php?id=62577)
			if (!($x = simplexml_load_file($fileOrUrl))) {

				if ($x === false) {

					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						echo $this->displayXmlError($error, $x);
					}

					libxml_clear_errors();
				}

				return;
			}

			if ($cnt < 0) {
				throw new Exception(self::ERROR_NOT_NON_NEGATIVE);
			}

			$selected = 0;

			foreach ($x->channel->item as $item) {

				$post        = new NewsPost();
				$post->link  = trim((string)$item->link);
				$post->title = trim((string)$item->title);
				$post->text  = trim((string)$item->description);

				$this->posts[] = $post;

				if ($cnt > 0) {
					++$selected;
				}
				if ($selected == $cnt && $cnt > 0) {
					break;
				}
			}
		}

		private function displayXmlError($error, $xml)
		{
			$return = $xml[$error->line - 1] . "\n";
			$return .= str_repeat('-', $error->column) . "^\n";

			switch ($error->level) {
				case LIBXML_ERR_WARNING:
					$return .= "Warning $error->code: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "Error $error->code: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "Fatal Error $error->code: ";
					break;
			}

			$return .= trim($error->message) .
			           "\n  Line: $error->line" .
			           "\n  Column: $error->column";

			if ($error->file) {
				$return .= "\n  File: $error->file";
			}

			return "$return\n\n--------------------------------------------\n\n";
		}

		/**
		 * @param $params - array of keys for NewsPost
		 */
		public function formatPrintAllFeeds(array $properties)
		{
			$existedProperties = array();
			foreach ($properties as $property){
				if (property_exists("NewsPost", $property)){
					$existedProperties[] = $property;
				} else {
					echo "Property '" . $property ."' doesn't exists" . PHP_EOL;
				}
			}

			$cnt = 1;
			foreach ($this->posts as $post) {
				echo $cnt . " " . PHP_EOL;

				foreach ($existedProperties as $property){
					echo $post->{$property} . PHP_EOL . PHP_EOL;
				}

				echo "--------------------------------------------" . PHP_EOL;

				++$cnt;
			}
		}
	}

	try{
		$news = new NewsFeed(RSS_URL, NEWS_COUNT);
		$news->formatPrintAllFeeds(array("link", "title", "text"));
	} catch (Exception $e){
		echo $e->getMessage();
	}





