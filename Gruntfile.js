module.exports = function(grunt) {
  'use strict';

  // load grunt tasks
  grunt.loadNpmTasks('grunt-contrib-handlebars');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // create configureation object
  grunt.initConfig({
    handlebars: {
      compile: {
        options: {
          // configure a namespace for your templates
          namespace: 'Handlebars.templates',

          // convert file path into a function name
          // in this example, I convert grab just the filename without the extension
          processName: function(filePath) {
            var pieces = filePath.split('/');
            return pieces[pieces.length - 1].split('.')[0];
          }

        },

        // output file: input files
        files: {
          'views/compiled/templates.js': 'views/client/*.hbs'
        }
      }
    },
    watch: {
      files: ['views/client/*.hbs'],
      tasks: ['handlebars']
    }
  });

}
