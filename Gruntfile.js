module.exports = function(grunt) {
	'use strict';

	require('load-grunt-tasks')(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Generate .pot file
		makepot: {
			target: {
				options: {
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					domainPath: 'languages', // Where to save the POT file.
					mainFile: '<%= pkg.name %>.php', // Main project file.
					potFilename: '<%= pkg.name %>.pot', // Name of the POT file.
					potHeaders: {
						'Report-Msgid-Bugs-To': 'https://github.com/seb86/WooCommerce-Cart-REST-API/issues',
						'language-team': 'Sébastien Dumont <mailme@sebastiendumont.com>',
						'language': 'en_US'
					},
					exclude: [
						'woo-dependencies/.*',
						'node_modules'
					]
				}
			}
		},

		checktextdomain: {
			options:{
				text_domain: '<%= pkg.name %>', // Project text domain.
				keywords: [
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d',
					'esc_attr_e:1,2d',
					'esc_attr_x:1,2c,3d',
					'_ex:1,2c,3d',
					'_n:1,2,4d',
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src:  [
					'*.php',
					'**/*.php', // Include all files
					'!woo-dependencies/**', // Exclude woo-dependencies/
					'!node_modules/**' // Exclude node_modules/
				],
				expand: true
			},
		},

		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: 'languages',
					src: ['*.po'],
					dest: 'languages',
					ext: '.mo',
					nonull: false
				}]
			}
		},

		// Bump version numbers (replace with version in package.json)
		replace: {
			Version: {
				src: [
					'readme.txt',
					'<%= pkg.name %>.php'
				],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:.*$/m,
						to: "Stable tag: <%= pkg.version %>"
					},
					{
						from: /Version:.*$/m,
						to: "Version:     <%= pkg.version %>"
					},
					{
						from: /public \$version = \'.*.'/m,
						to: "public $version = '<%= pkg.version %>'"
					}
				]
			}
		},

		// Copies the plugin to create deployable plugin.
		copy: {
			deploy: {
				src: [
					'**',
					'!.*',
					'!*.md',
					'!.*/**',
					'.htaccess',
					'!Gruntfile.js',
					'!package.json',
					'!package-lock.json',
					'!releases/**',
					'!node_modules/**',
					'!.DS_Store',
					'!npm-debug.log',
					'!*.sh',
					'!*.zip',
					'!*.jpg',
					'!*.jpeg',
					'!*.gif',
					'!*.png'
				],
				dest: '<%= pkg.name %>',
				expand: true,
				dot: true
			}
		},

		// Compresses the deployable plugin folder.
		compress: {
			zip: {
				options: {
					archive: './releases/<%= pkg.name %>-v<%= pkg.version %>.zip',
					mode: 'zip'
				},
				files: [
					{
						expand: true,
						cwd: './<%= pkg.name %>/',
						src: '**',
						dest: '<%= pkg.name %>'
					}
				]
			}
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: [ '<%= pkg.name %>' ]
	});

	// Set the default grunt command to run test cases.
	grunt.registerTask( 'default', [ 'test' ] );

	// Checks for errors.
	grunt.registerTask( 'test', [ 'checktextdomain' ]);

	// Checks for errors, updates version and runs i18n tasks.
	grunt.registerTask( 'dev', [ 'replace', 'makepot' ]);

	/**
	 * Run i18n related tasks.
	 *
	 * This includes extracting translatable strings, updating the master pot file.
	 * If this is part of a deploy process, it should come before zipping everything up.
	 */
	grunt.registerTask( 'update-pot', [ 'checktextdomain', 'makepot' ]);

	/**
	 * Creates a deployable plugin zipped up ready to upload
	 * and install on a WordPress installation.
	 */
	grunt.registerTask( 'zip', [ 'copy', 'compress', 'clean' ]);
};
