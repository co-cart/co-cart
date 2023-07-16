/**
 * Build automation scripts.
 *
 * @package CoCart
 */

module.exports = function (grunt) {
	"use strict";

	require("load-grunt-tasks")(grunt);

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		// Setting directories.
		dirs: {
			lang: "plugins/cocart/languages",
			php: "plugins/cocart/includes",
		},

		// Update developer dependencies
		devUpdate: {
			packages: {
				options: {
					packageJson: null,
					packages: {
						devDependencies: true,
						dependencies: false,
					},
					reportOnlyPkgs: [],
					reportUpdated: false,
					semver: true,
					updateType: "force",
				},
			},
		},

		// Generate .pot file
		makepot: {
			target: {
				options: {
					cwd: "plugins/build",
					domainPath: "languages", // Where to save the POT file.
					exclude: ["releases", "node_modules", "vendor"],
					mainFile: "<%= pkg.name %>.php", // Main project file.
					potComments:
						"Copyright (c) {year} Sébastien Dumont\nThis file is distributed under the same license as the CoCart package.", // The copyright at the beginning of the POT file.
					potFilename: "<%= pkg.name %>.pot", // Name of the POT file.
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						"x-poedit-keywordslist": true, // Include a list of all possible gettext functions.
						"Report-Msgid-Bugs-To":
							"https://github.com/co-cart/co-cart/issues",
						"language-team":
							"Sébastien Dumont <translate@cocart.xyz>",
						language: "en_US",
					},
					processPot: function (pot) {
						var translation,
							excluded_meta = [
								"Plugin Name of the plugin/theme",
								"Plugin URI of the plugin/theme",
								"Description of the plugin/theme",
								"Author of the plugin/theme",
								"Author URI of the plugin/theme",
							];

						for (translation in pot.translations[""]) {
							if (
								"undefined" !==
								typeof pot.translations[""][translation]
									.comments.extracted
							) {
								if (
									excluded_meta.indexOf(
										pot.translations[""][translation]
											.comments.extracted
									) >= 0
								) {
									console.log(
										"Excluded meta: " +
											pot.translations[""][translation]
												.comments.extracted
									);
									delete pot.translations[""][translation];
								}
							}
						}

						return pot;
					},
					type: "wp-plugin", // Type of project.
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
				},
			},
		},

		// Check strings for localization issues
		checktextdomain: {
			options: {
				text_domain: "<%= pkg.name %>", // Project text domain.
				keywords: [
					"__:1,2d",
					"_e:1,2d",
					"_x:1,2c,3d",
					"esc_html__:1,2d",
					"esc_html_e:1,2d",
					"esc_html_x:1,2c,3d",
					"esc_attr__:1,2d",
					"esc_attr_e:1,2d",
					"esc_attr_x:1,2c,3d",
					"_ex:1,2c,3d",
					"_n:1,2,4d",
					"_nx:1,2,4c,5d",
					"_n_noop:1,2,3d",
					"_nx_noop:1,2,3c,4d",
				],
			},
			files: {
				src: [
					"plugins/build/*.php",
					"plugins/build/**/*.php", // Include all files
					"!packages/**", // Exclude packages/
					"!node_modules/**", // Exclude node_modules/
					"!vendor/**", // Exclude vendor/
				],
				expand: true,
			},
		},

		potomo: {
			dist: {
				options: {
					poDel: false,
				},
				files: [
					{
						expand: true,
						cwd: "<%= dirs.lang %>",
						src: ["*.po"],
						dest: "<%= dirs.lang %>",
						ext: ".mo",
						nonull: false,
					},
				],
			},
		},

		// Bump version numbers (replace with version in package.json)
		replace: {
			php: {
				src: [
					"plugins/cocart/<%= pkg.name %>.php",
					"<%= dirs.php %>/classes/class-cocart.php",
				],
				overwrite: true,
				replacements: [
					{
						from: /Description:.*$/m,
						to: "Description: <%= pkg.description %>",
					},
					{
						from: /Requires at least:.*$/m,
						to: "Requires at least: <%= pkg.requires %>",
					},
					{
						from: /Requires PHP:.*$/m,
						to: "Requires PHP: <%= pkg.requires_php %>",
					},
					{
						from: /WC requires at least:.*$/m,
						to: "WC requires at least: <%= pkg.wc_requires %>",
					},
					{
						from: /WC tested up to:.*$/m,
						to: "WC tested up to: <%= pkg.wc_tested_up_to %>",
					},
					{
						from: /Version:.*$/m,
						to: "Version:     <%= pkg.version %>",
					},
					{
						from: /public static \$version = \'.*.'/m,
						to: "public static $version = '<%= pkg.version %>'",
					},
					{
						from: /public static \$required_wp = \'.*.'/m,
						to: "public static $required_wp = '<%= pkg.requires %>'",
					},
					{
						from: /public static \$required_woo = \'.*.'/m,
						to: "public static $required_woo = '<%= pkg.wc_requires %>'",
					},
					{
						from: /public static \$required_php = \'.*.'/m,
						to: "public static $required_php = '<%= pkg.requires_php %>'",
					},
				],
			},
			readme: {
				src: ["plugins/cocart/readme.txt"],
				overwrite: true,
				replacements: [
					{
						from: /Requires at least:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/im,
						to: "Requires at least:$1$2<%= pkg.requires %>$3",
					},
					{
						from: /Requires PHP:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/im,
						to: "Requires PHP:$1$2<%= pkg.requires_php %>$3",
					},
					{
						from: /Tested up to:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/im,
						to: "Tested up to:$1$2<%= pkg.tested_up_to %>$3",
					},
					{
						from: /WC requires at least:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/im,
						to: "WC requires at least:$1$2<%= pkg.wc_requires %>$3",
					},
					{
						from: /WC tested up to:(\*\*|)(\s*?)[a-zA-Z0-9.-]+(\s*?)$/im,
						to: "WC tested up to:$1$2<%= pkg.wc_tested_up_to %>$3",
					},
				],
			},
			stable: {
				src: ["plugins/build/readme.txt"],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:(\*\*|)(\s*?)[0-9.-]+(\s*?)$/im,
						to: "Stable tag:$1$2<%= pkg.version %>$3",
					},
				],
			},
			package: {
				src: ["plugins/cocart/load-package.php"],
				overwrite: true,
				replacements: [
					{
						from: /@version .*$/m,
						to: "@version <%= pkg.version %>",
					},
				],
			},
		},

		// Copies the plugin to create deployable plugin.
		copy: {
			firebuild: {
				files: [
					{
						expand: true,
						src: [
							"**",
							"!packages/**",
							"!node_modules/**",
							"!releases/**",
							"!tests/**",
							"!vendor/**",
							"!unit-tests/**",
						],
						dest: "fire-build/",
						dot: true,
					},
				],
			},
		},

		// Compresses the deployable plugin folder.
		compress: {
			zip: {
				options: {
					archive:
						"./releases/<%= pkg.name %>-v<%= pkg.version %>.zip",
					mode: "zip",
				},
				files: [
					{
						expand: true,
						cwd: "./plugins/build/",
						src: "**",
						dest: "<%= pkg.name %>",
					},
				],
			},
			firebuild: {
				options: {
					archive:
						'./releases/fire-builds/<%= pkg.name %>-v<%= pkg.version %>-<%= grunt.template.today("dS-mmmm-yyyy-HH-MM") %>.zip',
					mode: "zip",
				},
				files: [
					{
						expand: true,
						cwd: "./fire-build/",
						src: "**",
						dest: "",
					},
				],
			},
		},

		// Deletes the deployable plugin folder once zipped up.
		clean: {
			firebuild: ["fire-build/"],
		},
	}); // END of Grunt modules.

	// Set the default grunt command to run test cases.
	grunt.registerTask("default", ["test"]);

	// Checks for developer dependencies updates.
	grunt.registerTask("check", ["devUpdate"]);

	// Checks for errors.
	grunt.registerTask("test", ["checktextdomain"]);

	// Update version of plugin and package.
	grunt.registerTask("version", [
		"replace:php",
		"replace:readme",
		"replace:package",
	]);

	// Update stable version of plugin in readme.txt.
	grunt.registerTask("stable", ["replace:stable"]);

	/**
	 * Run i18n related tasks.
	 *
	 * This includes extracting translatable strings, updating the master pot file.
	 * If this is part of a deploy process, it should come before zipping everything up.
	 */
	grunt.registerTask("update-pot", ["checktextdomain", "makepot", "potomo"]);

	/**
	 * Creates a deployable plugin zipped up ready to upload
	 * and install on a WordPress installation.
	 */
	grunt.registerTask("zip", ["compress:zip"]);

	// Backup a copy of everything incase of emergency.
	grunt.registerTask("zipfire", [
		"copy:firebuild",
		"compress:firebuild",
		"clean:firebuild",
	]);

	// Build Plugin.
	grunt.registerTask("build", ["version", "update-pot", "zip"]);
	grunt.registerTask("fire", ["version", "update-pot", "zipfire"]);

	// Ready for release.
	grunt.registerTask("ready", ["version", "stable", "update-pot", "zip"]);
};
