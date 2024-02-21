/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils'
], function ($, utils) {
    'use strict';

    return function () {
        $.validator.addMethod(
            'validate-instance-id',
            function (value) {
                return utils.isEmptyNoTrim(value) || /^[A-Za-z0-9_-]+$/.test(value);
            },
            $.mage.__('Instance ID can contain only alphanumeric characters, underscores, and hyphens.')
        );

        $.validator.addMethod(
            'validate-workspace-configuration',
            function (value) {
                if (/^[*]+$/.test(value) || utils.isEmptyNoTrim(value)) {
                    return true;
                }

                function checkSchemaHasRequiredProperties(configProperties) {
                    let configSchemaProperties = ['project','workspace','details','credentials'];

                    for (let property of configSchemaProperties) {
                        if (!configProperties.hasOwnProperty(property)) {
                            return false;
                        }
                        configProperties = configProperties[property];
                    }
                    return true;
                }

                try {
                    let configProperties = JSON.parse(value);

                    if (typeof configProperties !== 'object') {
                        return false;
                    }
                    return checkSchemaHasRequiredProperties(configProperties);
                } catch (e) {
                    return false;
                }
            },
            $.mage.__('Workspace configuration file must have project, workspace and credentials details')
        );
    };
});
