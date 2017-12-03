# SNMP Based Monitoring Tools
There are 4 tools in this repository, all written in Perl programming language. All tools are developed and tested successfully on Ubuntu 14.04 LTS and may or may not work appropriately on other platforms.

## REPOSITORY CONTENTS
 * Improved-MRTG
 * Network-traffic-and-Server-performance-monitoring-tool
 * SNMP-based-device-status-monitoring-tool
 * Trap-Handler

## INTRODUCTION
Simple Network Management Protocol (SNMP), is an application layer protocol for managing and monitoring any SNMP enabled devices in a network such as routers, switches, servers, etc. The devices being managed using SNMP have SNMP agent application running on them. Management information such as sysName, sysUptime, ifSpeed, ifInOctets/ ifOutOctets, etc are collected by agent applications and stored in the Management Information Base (MIB). To monitor the devices, this information may be probed periodically.
Usually SNMP messages are exchanged in pairs i.e. requests probing SNMP information and responses from SNMP agents containing the concerned information.
SNMP transactions always originate from the SNMP managers, except in the case of SNMP traps. SNMP traps are asynchronous messages (no acknowledgment), sent by SNMP agents to SNMP managers to indicate the occurrence of an event such as device temperature exceeding a set threshold value. On receiving an SNMP trap, a manager executes an event handler that may either forward the trap to the Manager of Managers (MoM) or take counter measures to reduce the effect of the event.
This repository contains four SNMP based network devices and server monitoring tools. The functionality of each tool is explained in the readme files in their corresponding directory.

## REQUIREMENTS
The following packages should be installed for proper functioning of the tools,
 * MySQL
 * Apache v2.4.7
 * PHP v5.5.9
 * Perl v5.18.2
   Tool specific packages are specified in the corresponding tool's README.md file.

## USAGE INSTRUCTIONS
This section describes the general instructions for using the tools.
 * The entire repository of a tool should be placed in a location which can be serverd by the apache webserver i.e. in the document root of the apache webserver.

 * `db.conf` file holds the MySQL server credentials. This file should always be placed in the Tool's parent directory. For example, if the Tool's files like `index.php` is in `/home/james/snmp/` then `db.conf` should be placed in `/home/james/`.

 * All tools have the following files in common:

   - `index.php`, is the home page of the tool's web GUI.
   - `split.php`, PHP script that determine the path to `db.conf` and extract the MySQL server credentials which are used in other frontend scripts.
   - `bootstrap.min` and `bootstrap-theme.min` are minified bootstrap CSS files required for the web GUI.
   - `backend.pl`, is the main perl script that performs the SNMP operations concerned to the tool's functionality. (not applicable for TOOL3)
   - `backend.sh`, is a shell wrapper that executes `backend.pl` every 300 secs (TOOL1), 60 secs (TOOL2) and 30 secs (TOOL4). (not applicable for TOOL3)
   - `backend.pl` may be invoked through crontab instead of using the shell wrapper script.
