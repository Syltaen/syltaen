// =============================================================================
// > CONFIG FILE FOR TINY MCE
// =============================================================================

(function() {

    // ==================================================
    // > BUTTONS
    // ==================================================
    tinymce.create("tinymce.plugins.Syltaen", {
         init : function(ed, url) {

            /**
              * Inserts shortcode content
              */
              ed.addButton( 'button_eek', {
                  type: "splitbutton",
                   title: "",
                   icon: "bullist",
                   tooltip: "Liste",
                   cmd: "InsertUnorderedList",
                   menu: [
                       {
                           text: "test 1",
                           cmd: "InsertUnorderedList",
                           onClick: function () {
                                return_text = ed.selection.getContent() + ed.selection.getContent();
                                ed.execCommand("InsertUnorderedList", 0, return_text);
                                return_text = ed.selection.getContent() + ed.selection.getContent();
                                ed.execCommand("mceInsertContent", 0, return_text);
                           }
                       },
                       {
                        text: "test 2",
                        onClick: function () {
                             return_text = "test";
                             ed.execCommand("mceInsertContent", 0, return_text);
                        }
                    }
                   ]
              });

              /**
              * Adds HTML tag to selected content
              */
              ed.addButton( 'button_green', {
                   title : 'Add span',
                   image : '../wp-includes/images/smilies/icon_mrgreen.gif',
                   cmd: 'button_green_cmd'
              });

              ed.addCommand( 'button_green_cmd', function() {
                   var selected_text = ed.selection.getContent();
                   var return_text = '';
                   return_text = '<h1>' + selected_text + '</h1>';
                   ed.execCommand('mceInsertContent', 0, return_text);
              });
         }
    });

    // ==================================================
    // > REGISTER
    // ==================================================
    tinymce.PluginManager.add("sylaen", tinymce.plugins.Syltaen);
})();