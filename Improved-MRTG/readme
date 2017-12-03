===============================================================================================================
				MRTG Like Tool to monitor SNMP enabled network devices
===============================================================================================================


CONTENTS OF THIS FILE
---------------------

 * Description
 * Requirements




DESCRIPTION
-----------

	Multi Router Traffic Grapher (MRTG), is a tool that monitors interface traffic on SNMP enabled devices. It generates HTML pages containing RRD graphs of the inbound and outbound traffic on each interface. More information about MRTG is available at http://oss.oetiker.ch/mrtg/index.en.htm

	This tool has two functions. Firstly, configure MRTG to monitor inward and outward traffic through the interfaces of all devices in the MySQL database. This is achived by executing 'mrtgconf' script. Secondly, to design a tool that performs functions similar to MRTG but more efficiently. The tool uses SNMP to query all the devices in the MySQL database. The interfaces on each device are filtered based on the values of ifOperStatus, ifSpeed and ifType OIDs (Object Identifiers). Filtering is done to ensure that interfaces that are down or inactive are not probed. Traffic through other interfaces is ploted using RRDTool. RRDTool is an industry standard high performance data logging and graphing system for time-series data. More information about RRDTool is available at http://oss.oetiker.ch/rrdtool/index.en.html

	The tool is more efficient than MRTG in two aspects. Firstly, MRTG performs blocking SNMP calls i.e, devices are polled one after the other. If a device does not respond or takes longer time to respond, MRTG waits until the response is received and hence the overall time to query all the devices increases. This is eliminated in the tool by using non-blocking SNMP calls i.e, once an SNMP request is sent to a device the tool unlike MRTG does not stop until the response is received. Secondly, MRTG generates graphs which are stored on the hard disk forever (unless deleted manually). If there are many devices each having too many interfaces, then large number of images are to be stored on the hard disk. This is solved by deleting the images after displaying on the frontend.




REQUIREMENTS
------------

 * MRTG (need to use MRTG configuration funtion of the tool)
 * RRDTool
 * Perl modules:

   - Net::SNMP
   - RRD::Simple
   - Data::Dumper


 * PHP modules:

   - php-rrd
