(function($) {
    $(document).ready(function() {
        /**
         * Set up the CFS Yoast Analysis plugin
         */
        var CFSYoastAnalysis = function() {

            // Register with YoastSEO
            YoastSEO.app.registerPlugin('CFSYoastAnalysis', {status: 'ready'});
            YoastSEO.app.registerModification('content', this.addCfsFieldsToContent, 'CFSYoastAnalysis', 5);

            this.analysisTimeout = 0;
            this.bindListeners();

            // Re-analyse SEO score each time a row is added to a Loop
            $('.cfs_add_field').on('cfs/ready', this.bindListeners);
        };

        /**
         * Bind listeners to text fields (input and textarea)
         */
        CFSYoastAnalysis.prototype.bindListeners = function() {
            $('#post-body, #edittag').find('input[type="text"][name^="cfs"], textarea[name^="cfs"]').on('keyup paste cut blur', function() {
                if ( CFSYoastAnalysis.analysisTimeout ) {
                    window.clearTimeout(CFSYoastAnalysis.analysisTimeout);
                }
                CFSYoastAnalysis.analysisTimeout = window.setTimeout( function() { YoastSEO.app.pluginReloaded('CFSYoastAnalysis'); }, 200 );
            });
        };

        /**
         * Combine the content of all CFS fields on the page and add it to Yoast content analysis
         *
         * @param data Current page content
         */
        CFSYoastAnalysis.prototype.addCfsFieldsToContent = function(data) {
            var cfs_content = ' ';

            $('#post-body, #edittag').find('input[type="text"][name^="cfs"], textarea[name^="cfs"]').each(function() {
                cfs_content += ' ' + $(this).val();
            });

            data = data + cfs_content;

            return data.trim();
        };

        new CFSYoastAnalysis();
    });
}(jQuery));