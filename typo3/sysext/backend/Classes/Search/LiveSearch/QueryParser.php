<?php
namespace TYPO3\CMS\Backend\Search\LiveSearch;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class for parsing query parameters in backend live search.
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Jeff Segars <jeff@webempoweredchurch.org>
 */
class QueryParser {

	/**
	 * @var string
	 */
	protected $commandKey = '';

	/**
	 * @var string
	 */
	protected $tableName = '';

	/**
	 * @var string
	 */
	const COMMAND_KEY_INDICATOR = '#';

	/**
	 * @var string
	 */
	const COMMAND_SPLIT_INDICATOR = ':';

	/**
	 * Retrieve the validated command key
	 *
	 * @param string $query
	 * @return string Command name
	 */
	protected function extractKeyFromQuery($query) {
		$keyAndValue = substr($query, 1);
		$key = explode(':', $keyAndValue);
		$this->commandKey = $key[0];
	}

	/**
	 * Extract the search value from the full search query which contains also the command part.
	 *
	 * @param string $query For example #news:weather
	 * @return string The extracted search value
	 */
	public function getSearchQueryValue($query) {
		$this->extractKeyFromQuery($query);
		return str_replace(self::COMMAND_KEY_INDICATOR . $this->commandKey . self::COMMAND_SPLIT_INDICATOR, '', $query);
	}

	/**
	 * Find the registered table command and retrieve the matching table name.
	 *
	 * @param string $query
	 * @return string Database Table name
	 */
	public function getTableNameFromCommand($query) {
		$tableName = '';
		$this->extractKeyFromQuery($query);
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch']) && array_key_exists($this->commandKey, $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'])) {
			$tableName = $GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'][$this->commandKey];
		}
		return $tableName;
	}

	/**
	 * Verify if an given query contains a page jump command.
	 *
	 * @param string $query A valid value looks like '#14'
	 * @return int
	 */
	public function getId($query) {
		return str_replace(self::COMMAND_KEY_INDICATOR, '', $query);
	}

	/**
	 * Verify if a given query contains a page jump command.
	 *
	 * @param string $query A valid value looks like '#14'
	 * @return bool
	 */
	public function isValidPageJump($query) {
		$isValid = FALSE;
		if (preg_match('~^#(\\d)+$~', $query)) {
			$isValid = TRUE;
		}
		return $isValid;
	}

	/**
	 * Verify if an given query contains an registered command key.
	 *
	 * @param string $query
	 * @return bool
	 */
	public function isValidCommand($query) {
		$isValid = FALSE;
		if (strpos($query, self::COMMAND_KEY_INDICATOR) === 0 && strpos($query, self::COMMAND_SPLIT_INDICATOR) > 1 && $this->getTableNameFromCommand($query)) {
			$isValid = TRUE;
		}
		return $isValid;
	}

	/**
	 * Gets the command for the given table.
	 *
	 * @param string $tableName The table to find a command for.
	 * @return string
	 */
	public function getCommandForTable($tableName) {
		$commandArray = array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['livesearch'], $tableName);
		if (is_array($commandArray)) {
			$command = $commandArray[0];
		} else {
			$command = FALSE;
		}
		return $command;
	}

	/**
	 * Gets the page jump command for a given query.
	 *
	 * @param string $query
	 * @return string
	 */
	public function getCommandForPageJump($query) {
		if ($this->isValidPageJump($query)) {
			$command = $this->getCommandForTable('pages');
			$id = $this->getId($query);
			$resultQuery = self::COMMAND_KEY_INDICATOR . $command . self::COMMAND_SPLIT_INDICATOR . $id;
		} else {
			$resultQuery = FALSE;
		}
		return $resultQuery;
	}

}