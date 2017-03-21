module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Check textdomain errors.
		checktextdomain: {
			options:{
				text_domain: 'postmark-wordpress',
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
					'**/*.php', // Include all files
					'!apigen/**', // Exclude apigen/
					'!lib/**', // Exclude lib (outside libraries)
					'!node_modules/**', // Exclude node_modules/
				],
				expand: true
			}
		},

		// Generate POT files.
		makepot: {
			target: {
				options: {
					domainPath: '/languages/',
					mainFile: 'postmark.php',
					potFilename: 'postmark.pot',
					type: 'wp-plugin',
					exclude: [],
					include: [],
					potHeaders: {
                    	poedit: true,
                    	'language': 'us_US',
                    	'last-translator': 'Renato Alves <contato@ralv.es>',
											'language-team': 'Postmark',
                    	'report-msgid-bugs-to': 'https://postmarkapp.com/',
                    	'plural-forms': 'nplurals=2; plural=n != 1;',
					    'x-poedit-country': 'Brasil',
					    'x-poedit-language': 'Portuguese',
					    'x-poedit-sourcecharset': 'UTF-8',
					    'x-poedit-basepath': '.\n',
					    'x-poedit-keywordslist': '__;_e;_x;esc_html_e;esc_html__;esc_attr_e;esc_attr__;_ex:1,2c;_nx:4c,1,2;_nx_noop:4c,1,2;_x:1,2c;_n:1,2;_n_noop:1,2;__ngettext_noop:1,2;_c,_nc:4c,1,2;',
					    'x-textdomain-support': 'yes'
                	},
					updateTimestamp: true,
                	updatePoFiles: true
				}
			}
		},
	});

	require('load-grunt-tasks')(grunt);
};
