<?php
/*
 * api.php
 *
 * part of pfSense (https://www.pfSense.org/)
 * Copyright (c) 2023 CrowdSec
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once("util.inc");
require_once("globals.inc");


$default = json_encode([]);
$method = $_SERVER['REQUEST_METHOD'] ?? '';

function getServiceStatus($service) {
    $status = trim(shell_exec("service $service onestatus"));
    if (strpos($status, "not running") !== false) {
        return "stopped";
    } elseif (strpos($status, "is running") !== false) {
        return "running";
    }
    return "unknown";
}

if ($method === 'DELETE' && isset($_GET['action']) && isset($_GET['decision_id'])) {
    $id = (int) strip_tags($_GET['decision_id']);
    $action = strip_tags($_GET['action']);
    if ($id > 0 && $action === 'status-decision-delete') {
        $ret = mwexec("/usr/local/bin/cscli --error decisions delete --id $id");
        if ($ret === 0){
            echo json_encode(['message' => 'OK']);
        }
        else {
            echo $default;
        }
    } else {
        echo $default;
    }
} elseif ($method === 'POST' && isset($_POST['action'])) {
    $action = strip_tags($_POST['action']);

    switch ($action) {
        case 'status-alerts-list':
            echo shell_exec("/usr/local/bin/cscli alerts list -l 0 -o json");
            break;
        case 'status-bouncers-list':
            echo shell_exec("/usr/local/bin/cscli bouncers list -o json");
            break;
        case 'status-decisions-list':
            echo shell_exec("/usr/local/bin/cscli decisions list -l 0 -o json");
            break;
        case 'status-machines-list':
            echo shell_exec("/usr/local/bin/cscli machines list -o json");
            break;
        case 'status-collections-list':
        case 'status-parsers-list':
        case 'status-postoverflows-list':
        case 'status-scenarios-list':
        case 'status-appsec-configs-list':
        case 'status-appsec-rules-list':
        case 'status-contexts-list':
            echo shell_exec("/usr/local/bin/cscli hub list -o json");
            break;
        case 'metrics-acquisition-list':
        case 'metrics-bucket-list':
        case 'metrics-parser-list':
        case 'metrics-alerts-list':
        case 'metrics-lapi-machines-list':
        case 'metrics-lapi-list':
        case 'metrics-lapi-bouncers-list':
        case 'metrics-decisions-list';
            echo shell_exec("/usr/local/bin/cscli metrics -o json");
            break;
        case 'services-status':
            echo json_encode(
                [
                    'crowdsec-status' => getServiceStatus('crowdsec'),
                    'crowdsec-firewall-status'=> getServiceStatus('crowdsec_firewall'),
                ]);
            break;
        default:
            echo $default;
    }
} else {
    echo $default;
}


