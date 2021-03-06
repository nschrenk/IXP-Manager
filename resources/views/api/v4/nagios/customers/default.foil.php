#
# This file contains Nagios configuration for customers' VLAN interfaces
#
# WARNING: this file is automatically generated using the
#   api/v4/nagios/customers/{vlanid} API call to IXP Manager.
#   Any local changes made to this script will be lost.
#
# VLAN id: <?= $t->vlan->getId() ?>; tag: <?= $t->vlan->getNumber() ?>; name: <?= $t->vlan->getName() ?>.
#
# Generated: <?= date( 'Y-m-d H:i:s' ) . "\n" ?>
#

<?php
    // some arrays for later:
    $all       = [];
    $cabinets  = [];
    $locations = [];
?>

<?php foreach( $t->vlis as $vli ): ?>

###############################################################################################
###
### <?= $vli['cname'] . "\n" ?>
###
### <?= $vli['locname'] ?> / <?= $vli['cabname'] ?> / <?=  $vli['sname'] ?>.
###

<?php foreach( [ 'ipv4', 'ipv6' ] as $p ):

    if( !$vli[$p.'enabled'] || !$vli[$p.'address'] ) {
        echo "\n\n ## {$p} not enabled / no address configured, skipping\n\n";
        continue;
    }

    $hostname = preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $vli['caname'] ) ) . '-as' . $vli['cautsys'] . '-' . $p . '-vlantag' . $vli['vtag'] . '-vliid' . $vli['vliid'];

    $all[]                          = $hostname;
    $cabinets[ $vli['cabname'] ][]  = $hostname;
    $locations[ $vli['locname'] ][] = $hostname;
?>

### Host: <?= $p ?>: <?= $vli[$p.'address'] ?> / <?= $vli[$p.'hostname'] ?> / <?= $vli['vname'] ?>.

define host {
    use                     generic-host
    host_name               <?= $hostname ?>

    alias                   <?= $vli['cname'] ?> / <?= $vli['sname'] ?> / <?= $vli['vname'] ?>.
    address                 <?= $vli[$p.'address'] ?>

    check_command           check-host-alive
    max_check_attempts      10
    notification_interval   120
    notification_period     24x7
    notification_options    d,u,r
    contact_groups          admins
}



### Service: <?= $p ?>: <?= $vli[$p.'address']  ?> / <?= $vli[$p.'hostname'] ?> / <?= $vli['vname'] ?>.

define service {
    use                     generic-service
    host_name               <?= $hostname ?>

    check_period            24x7
    max_check_attempts      3
    normal_check_interval   5
    retry_check_interval    1
    contact_groups          admins
    notification_interval   120
    notification_period     24x7
    notification_options    w,u,c,r
    service_description     PING<?= $vli['busyhost'] ? '-busy' : '' ?>

    check_command           check_ping!<?= $vli['busyhost'] ? '1000.0,80%!2000.0,90%' : '250.0,20%!500.0,60%' ?>

}

<?php endforeach; ?>

<?php endforeach; ?>



###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################
###############################################################################################


###############################################################################################
###
### Group: by cabinet
###
###
###

<?php foreach( $cabinets as $k => $c ): ?>

define hostgroup {
    hostgroup_name  cabinet-vlanid-<?= $t->vlan->getId() ?>-<?= preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $k ) ) ?>

    alias           All Members in Cabinet <?= $k ?> for VLAN <?= $t->vlan->getName() ?>

    members         <?= $t->softwrap( $c, 1, ', ', ',', 20 ) ?>

}

<?php endforeach; ?>


###############################################################################################
###
### Group: by location
###
###
###

<?php foreach( $locations as $k => $l ): ?>

define hostgroup {
    hostgroup_name  location-vlanid-<?= $t->vlan->getId() ?>-<?= preg_replace( '/[^a-zA-Z0-9]/', '-', strtolower( $k ) ) ?>

    alias           All Members at Location <?= $k ?> for VLAN <?= $t->vlan->getName() ?>

    members         <?= $t->softwrap( $l, 1, ', ', ',', 20 ) ?>

}

<?php endforeach; ?>


###############################################################################################
###
### Group: all
###
###
###

define hostgroup {
    hostgroup_name  all-vlanid-<?= $t->vlan->getId() ?>

    alias           All Members for VLAN <?= $t->vlan->getName() ?>

    members         <?= $t->softwrap( $all, 1, ', ', ',', 20 ) ?>

}

