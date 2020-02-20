<?php

namespace XWP\WPCOMVIPPlugins;

use Composer\Script\Event;

class ConfigBuilder
{
	public static function build(Event $event)
	{
		$vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
		$plugins_dir = sprintf('%s/automattic/vip-wpcom-plugins', $vendorDir);
		$satis_config_file = sprintf('%s/satis.json', dirname( __DIR__ ));

		$repositories = array_map(
			function ($plugin) use ($plugins_dir) {
				$plugin_dir_path = sprintf('%s/%s', $plugins_dir, $plugin);
				$revisions = self::getDirectorySvnRevisions($plugin_dir_path);
				$latestRevision = reset($revisions);

				return [
					'type' => 'package',
					'package' => [
						'name' => 'xwp-vip-wpcom-plugins/' . $plugin,
						'type' => 'wordpress-plugin',
						'version' => 'dev-master',
						'source' => [
							'url' => 'https://vip-svn.wordpress.com/plugins',
							'type' => 'svn',
							'reference' => sprintf('%s@%s', $plugin, $latestRevision),
						],
						'dist' => [
							'url' => $plugin_dir_path,
							'type' => 'path',
						],
					],
				];
			},
			array_map('basename', self::getDirectories($plugins_dir) )
		);

		$config = json_encode( [
			'name' => 'xwp/vip-wpcom-plugins',
			'homepage' => 'https://xwp.github.io/vip-wpcom-plugins',
			'require-all' => true,
			'repositories' => $repositories,
			'archive' => [
				'directory' => 'dist',
				'format' => 'zip',
			],
		] );

		file_put_contents($satis_config_file, $config);
	}

	protected static function getDirectories($path) {
		$pattern = sprintf('%s/*', rtrim($path, '/\\'));

		return glob($pattern , GLOB_ONLYDIR);
	}

	protected static function getDirectorySvnRevisions($path) {
		$logCommand = sprintf('svn log --xml %s', $path);
		$xml = simplexml_load_string(shell_exec($logCommand));
		$revisions = [];

		foreach ($xml->logentry as $logentry) {
			$revisions[] = (int) $logentry->attributes()->revision;
		}

		return $revisions;
	}
}
