#
# Copyright (C) 1998 Ken MacLeod
# Frontier::Daemon is free software; you can redistribute it
# and/or modify it under the same terms as Perl itself.
#
# $Id$
#

# NOTE: see Net::pRPC for a Perl RPC implementation

###
### NOTE: $self is inherited from HTTP::Daemon and the weird access
### comes from there (`${*$self}').
###

use strict;

package Frontier::Daemon;
use vars qw{@ISA};

@ISA = qw{HTTP::Daemon};

use Frontier::RPC2;
use HTTP::Daemon;
use HTTP::Status;

sub new {
    my $class = shift; my %args = @_;
    my $self = $class->SUPER::new(%args);
    return undef unless $self;

    ${*$self}{'methods'} = $args{'methods'};
    ${*$self}{'decode'} = new Frontier::RPC2;
    ${*$self}{'response'} = new HTTP::Response 200;
    ${*$self}{'response'}->header('Content-Type' => 'text/xml');

    my $conn;
    while ($conn = $self->accept) {
	my $rq = $conn->get_request;
	if ($rq) {
	    if ($rq->method eq 'POST' && $rq->url->path eq '/RPC2') {
                ${*$self}{'response'}->content(${*$self}{'decode'}->serve($rq->content, ${*$self}{'methods'}));
                $conn->send_response(${*$self}{'response'});
	    } else {
		$conn->send_error(RC_FORBIDDEN);
	    }
	}
	$conn = undef;		# close connection
    }
}

=head1 NAME

Frontier::Daemon - receive Frontier XML RPC requests

=head1 SYNOPSIS

 use Frontier::Daemon;

 Frontier::Daemon->new(methods => {
     'rpcName' => \&sub_name,
        ...
     });

=head1 DESCRIPTION

I<Frontier::Daemon> is an HTTP/1.1 server that listens on a socket for
incoming requests containing Frontier XML RPC2 method calls.
I<Frontier::Daemon> is a subclass of I<HTTP::Daemon>, which is a
subclass of I<IO::Socket::INET>.

I<Frontier::Daemon> takes a `C<methods>' parameter, a hash that maps
an incoming RPC method name to reference to a subroutine.

=head1 SEE ALSO

perl(1), HTTP::Daemon(3), IO::Socket::INET(3), Frontier::RPC2(3)

<http://www.scripting.com/frontier5/xml/code/rpc.html>

=head1 AUTHOR

Ken MacLeod <ken@bitsko.slc.ut.us>

=cut

1;
