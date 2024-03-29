<?php

/**
 * Akeeba Kickstart
 * A JSON-powered archive extraction tool
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / AkeebaBackup.com
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */
class AKKickstartUtils
{
	/**
	 * Guess the best path containing backup archives. The default strategy is check in the current directory first,
	 * then attempt to find an Akeeba Backup for Joomla!, Akeeba Solo or Akeeba Backup for WordPress default backup
	 * output directory under the current root. The first one containing backup archives wins.
	 *
	 * @return string The path to get archives from
	 */
	public static function getBestArchivePath()
	{
		$basePath = self::getPath();
		$basePathSlash = (empty($basePath) ? '.' : rtrim($basePath, '/\\')) . '/';

		$paths = array(
			$basePath,
			$basePathSlash . 'administrator/components/com_akeeba/backup',
			$basePathSlash . 'backups',
			$basePathSlash . 'wp-content/plugins/akeebabackupwp/app/backups',
		);

		foreach ($paths as $path)
		{
			$archives = self::findArchives($path);

			if (!empty($archives))
			{
				return $path;
			}
		}

		return $basePath;
	}

	/**
	 * Gets the directory the file is in
	 *
	 * @return string
	 */
	public static function getPath()
	{
		$path = KSROOTDIR;
		$path = rtrim(str_replace('\\', '/', $path), '/');
		if (!empty($path))
		{
			$path .= '/';
		}

		return $path;
	}

	/**
	 * Scans the current directory for archive files (JPA, JPS and ZIP format)
	 *
	 * @param string $path The path to look for archives. null for automatic path
	 *
	 * @return array
	 */
	public static function findArchives($path)
	{
		$ret = array();

		if (empty($path))
		{
			$path = self::getPath();
		}

		if (empty($path))
		{
			$path = '.';
		}

		$dh = @opendir($path);

		if ($dh === false)
		{
			return $ret;
		}

		while (false !== $file = @readdir($dh))
		{
			$dotpos = strrpos($file, '.');

			if ($dotpos === false)
			{
				continue;
			}

			if ($dotpos == strlen($file))
			{
				continue;
			}

			$extension = strtolower(substr($file, $dotpos + 1));

			if (in_array($extension, array('jpa', 'zip', 'jps')))
			{
				$ret[] = $file;
			}
		}

		closedir($dh);

		if (!empty($ret))
		{
			return $ret;
		}

		// On some hosts using opendir doesn't work. Let's try Dir instead
		$d = dir($path);

		while (false != ($file = $d->read()))
		{
			$dotpos = strrpos($file, '.');

			if ($dotpos === false)
			{
				continue;
			}

			if ($dotpos == strlen($file))
			{
				continue;
			}

			$extension = strtolower(substr($file, $dotpos + 1));

			if (in_array($extension, array('jpa', 'zip', 'jps')))
			{
				$ret[] = $file;
			}
		}

		return $ret;
	}

	/**
	 * Scans the current directory for archive files and returns them as <OPTION> tags
	 *
	 * @param string $path The path to look for archives. null for automatic path
	 *
	 * @return string
	 */
	public static function getArchivesAsOptions($path = null)
	{
		$ret = '';

		$archives = self::findArchives($path);

		if (empty($archives))
		{
			return $ret;
		}

		foreach ($archives as $file)
		{
			//$file = htmlentities($file);
			$ret .= '<option value="' . $file . '">' . $file . '</option>' . "\n";
		}

		return $ret;
	}
}