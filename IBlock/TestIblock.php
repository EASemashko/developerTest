<?php
	require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

	use Bitrix\Main\Loader;

	class TestIblock
	{
		public const CACHE_TIME = 3600;

		public static function GetList(
			$order = array("NAME" => "ASC"),
			$filter = array(),
			$select = array("ID")
		) {

			if (!Loader::includeModule("iblock")) {
				\Bitrix\Main\ErrorCollection::add(
					array("Iblock is not included"));

				return false;
			}

			$arResult                = array();
			$lifeTime                = self::CACHE_TIME;
			$cacheParams             = $filter;
			$cacheParams['func']     = 'Bitrix\Iblock\ElementTable::getList';
			$cacheParams['arSelect'] = $select;
			$cacheParams['sort']     = $order;
			$cacheID                 = md5(serialize($cacheParams));

			if (Bitrix\Main\Data\Cache::initCache($lifeTime, $cacheID, "/")) {
				$arResult = Bitrix\Main\Data\Cache::getVars();
			} else {
				$elements = Bitrix\Iblock\ElementTable::getList($order, $filter, false, false, $select);
				while ($arElement = $elements->GetNext()) {
					$arResult[] = $arElement;
				}
			}

			if (Bitrix\Main\Data\Cache::startDataCache()) {
				Bitrix\Main\Data\Cache::endDataCache($arResult);
			}

			return $arResult;
		}
	}