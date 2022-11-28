// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings JS.
 *
 * @package
 * @copyright Copyright (c) 2017 Open LMS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/str', 'core/templates'], function($, str, templates) {

    return {
        /**
         * Initialising function
         * @param {int} contextId
         */
        init: function(contextId) {

            var modified = false,
                strings = {};

            /**
             * Check that settings fields have been completed.
             * @param {array} flds
             * @returns {boolean}
             */
            var checkFieldsComplete = function(flds) {
                for (var f in flds) {
                    var fld = flds[f];
                    var val = '' + $('#id_s_collaborate_' + fld).val().trim();
                    if (val === '') {
                        return false;
                    }
                }
                return true;
            };

            /**
             * Check that REST settings have been completed.
             * @returns {boolean}
             */
            var checkRESTFieldsComplete = function() {
                var flds = ['restserver', 'restkey', 'restsecret'];
                return checkFieldsComplete(flds);
            };

            /**
             * Check that SOAP settings have been compelted.
             * @returns {boolean}
             */
            var checkSOAPFieldsComplete = function() {
                var flds = ['server', 'username', 'password'];
                return checkFieldsComplete(flds);
            };

            /**
             * Render new api status message.
             *
             * @param {string} stringKey
             * @param {string} alertClass
             * @param {string} extraClasses
             * @param {string} api
             */
            var apiMsg = function(stringKey, alertClass, extraClasses, api) {
                var msg = strings[stringKey];

                var msgContainer = $('#api_diag .noticetemplate_' + alertClass).children().first().clone();

                $(msgContainer).addClass(extraClasses);
                $(msgContainer).addClass(api);
                $(msgContainer).html('<span class="api-connection-msg">' + api + ' - ' + msg + '</span>');

                // Wipe out existing connection status msg container.
                $('#api_diag .api-connection-status .undefined').remove();
                $('#api_diag .api-connection-status .' + api).remove();

                // Put in new msg container.
                $('#api_diag .api-connection-status').append($(msgContainer));
            };

            /**
             * Test api.
             */
            var testRestApi = function() {
                var dataRest;

                if (checkRESTFieldsComplete()) {
                    apiMsg('verifyingapi', 'message', 'spinner', 'REST');
                    dataRest = {
                        'server': $('#id_s_collaborate_restserver').val().trim(),
                        'restkey': $('#id_s_collaborate_restkey').val().trim(),
                        'restsecret': $('#id_s_collaborate_restsecret').val() // Never trim secrets!
                    };
                    dataRest.contextid = contextId;
                    $.ajax({
                        url: M.cfg.wwwroot + '/mod/collaborate/testapi.php',
                        context: document.body,
                        data: dataRest,
                        success: function(data) {
                            if (data.success) {
                                if (!modified) {
                                    apiMsg('connectionverified', 'success', '', 'REST');
                                } else {
                                    apiMsg('connectionverifiedchanged', 'success', '', 'REST');
                                }
                            } else {
                                apiMsg('connectionfailed', 'problem', '', 'REST');
                            }
                        },
                        error: function() {
                            apiMsg('connectionfailed', 'problem', '', 'REST');
                        }
                    });
                }
            };

            var testSoapApi = function() {
                var dataSoap;

                // If REST credentials are set only the message with Credentials Verified will show up.
                if (checkSOAPFieldsComplete()) {
                    if (!checkRESTFieldsComplete()) {
                        apiMsg('verifyingapi', 'message', 'spinner', 'SOAP');
                    }
                    dataSoap = {
                        'server': $('#id_s_collaborate_server').val().trim(),
                        'username': $('#id_s_collaborate_username').val().trim(),
                        'password': $('#id_s_collaborate_password').val() // Never trim passwords!
                    };

                    dataSoap.contextid =  contextId;
                    $.ajax({
                        url: M.cfg.wwwroot + '/mod/collaborate/testapi.php',
                        context: document.body,
                        data: dataSoap,
                        success: function(data) {
                            if (data.success) {
                                if (!modified) {
                                    apiMsg('connectionverified', 'success', '', 'SOAP');
                                } else {
                                    apiMsg('connectionverifiedchanged', 'success', '', 'SOAP');
                                }
                                $('.soapapisettings').css('display', 'block');
                            } else {
                                if (!checkRESTFieldsComplete()) {
                                    apiMsg('connectionfailed', 'problem', '', 'SOAP');
                                }
                            }
                        },
                        error: function() {
                            if (!checkRESTFieldsComplete()) {
                                apiMsg('connectionfailed', 'problem', '', 'SOAP');
                            }
                        }
                    });
                }
            };

            /**
             * Apply listener for api test button.
             *
             * @author Guy Thomas
             */
            var applyClickApiTest = function() {
                $('.api_diag_btn').click(function(e) {
                    e.preventDefault();
                    testRestApi();
                    testSoapApi();
                });
            };

            /**
             * Apply listener for when settings changed.
             *
             * @author Guy Thomas
             */
            var applySettingChangeCheck = function() {
                var settingfields = '#id_s_collaborate_server, #id_s_collaborate_username, #id_s_collaborate_password';
                $(settingfields).keypress(function() {
                    modified = true;
                });
            };

            str.get_strings([
                {key: 'connectionfailed', component: 'mod_collaborate'},
                {key: 'connectionverified', component: 'mod_collaborate'},
                {key: 'verifyingapi', component: 'mod_collaborate'},
                {key: 'connectionstatusunknown', component: 'mod_collaborate'}
            ]).then(function(s){

                strings.connectionfailed = s[0];
                strings.connectionverified = s[1];
                strings.verifyingapi = s[2];
                strings.connectionstatusunknown = s[3];

                applySettingChangeCheck();
                applyClickApiTest();

                if (checkRESTFieldsComplete()) {
                    testRestApi();
                }
                if (checkSOAPFieldsComplete()) {
                    testSoapApi();
                }
                // For IE / Edge, disable fieldset fields.
                if (/Edge\/\d./i.test(navigator.userAgent)
                    || /MSIE/i.test(navigator.userAgent)
                    || /Trident/i.test(navigator.userAgent)) {
                    $('fieldset[disabled="true"] input').attr('disabled', 'true');
                }

                // If REST settings not complete then reveal SOAP settings.
                if (!checkRESTFieldsComplete()) {
                    $('.soapapisettings').css('display', 'block');
                }

            });

        },

        // Method to initialize UI fixes.
        uiinit: function(migrationStatus) {
            if (migrationStatus && migrationStatus < 5) {
                let selector = '#page-mod-collaborate-mod section#region-main div[role=\'main\']';
                if (M.cfg.theme === 'snap') {
                    selector = '#page-mod-collaborate-mod main div[role=\'main\']';
                }
                const targetNode = document.querySelector(selector);
                if (targetNode) {
                    targetNode.setAttribute('style', 'display: none;');
                }
            }
            const targetNode = document.getElementById('id_largesessionenable');
            const config = { attributes: true};
            const callback = function(mutationsList) {
                for (const i in mutationsList) {
                    const mutation = mutationsList[i];
                    if (mutation.type === 'attributes' && mutation.attributeName === 'disabled') {
                        const selectNode = $('[id="id_largesessionenable"]');
                        const disabled = selectNode.is(':disabled');
                        // Remove all alerts if they exist
                        if ($('.collab-alert').length > 0) {
                            $('.collab-alert').remove();
                        }
                        if (disabled) {
                            (function() {
                                return str.get_strings([
                                    {key: 'optionnotavailableforgroups', component: 'mod_collaborate'}
                                ]);
                            })()
                                .then(function(localizedstring){
                                    if (M.cfg.theme === 'snap') {
                                        return templates.render('theme_snap/form_alert', {
                                            type: 'warning',
                                            classes: 'collab-alert',
                                            message: localizedstring
                                        });
                                    } else {
                                        return '<div class="alert alert-warning collab-alert" role="alert">' +
                                            localizedstring +
                                            '</div>';
                                    }
                                })
                                .then(function (html) {
                                    // Add warning.
                                    selectNode.parent().parent().parent().append(html);
                                    if (M.cfg.theme === 'snap') {
                                        // Colors for disabling the divs.
                                        selectNode.parent().parent().parent().addClass('mod-collaborate-dimmed-option');
                                    }
                                });
                        } else {
                            if (M.cfg.theme === 'snap') {
                                selectNode.parent().parent().parent().removeClass('mod-collaborate-dimmed-option');
                            }
                        }
                    }
                }
            };
            const observer = new MutationObserver(callback);
            observer.observe(targetNode, config);
        }
    };
});
