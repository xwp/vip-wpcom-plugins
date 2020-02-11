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
			function ($plugin) {
				return [
					'type' => 'package',
					'package' => [
						'name' => 'xwp-vip-wpcom-plugins/' . $plugin,
						'url' => 'https://vip-svn.wordpress.com/plugins',
						'type' => 'wordpress-plugin',
						'version' => 'dev-master',
						'source' => [
							'url' => 'https://vip-svn.wordpress.com/plugins',
							'type' => 'svn',
							'reference' => $plugin,
						],
					],
				];
			},
			array_map('basename', self::getDirectories($plugins_dir))
		);

		$config = json_encode( [
			'name' => 'xwp/vip-wpcom-plugins',
			'homepage' => 'https://xwp.github.io/vip-wpcom-plugins',
			'require-all' => true,
			'repositories' => $repositories,
		] );

		file_put_contents($satis_config_file, $config);
	}

	protected static function getDirectories($path) {
		$pattern = sprintf('%s/*', rtrim($path, '/\\'));

		return glob($pattern , GLOB_ONLYDIR);
	}
}
