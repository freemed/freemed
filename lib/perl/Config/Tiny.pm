package Config::Tiny;

# If you thought Config::Simple was small...

use 5.004;
use strict;

use vars qw{$VERSION $errstr};
BEGIN {
	$VERSION = '2.00';
	$errstr  = '';
}

# Create an empty object
sub new { bless {}, shift }

# Create an object from a file
sub read {
	my $class = ref $_[0] ? ref shift : shift;

	# Check the file
	my $file = shift or return $class->_error( 'You did not specify a file name' );
	return $class->_error( "File '$file' does not exist" )              unless -e $file;
	return $class->_error( "'$file' is a directory, not a file" )       unless -f _;
	return $class->_error( "Insufficient permissions to read '$file'" ) unless -r _;

	# Slurp in the file
	local $/ = undef;
	open CFG, $file or return $class->_error( "Failed to open file '$file': $!" );
	my $contents = <CFG>;
	close CFG;

	$class->read_string( $contents );
}

# Create an object from a string
sub read_string {
	my $class = ref $_[0] ? ref shift : shift;
	my $self  = bless {}, $class;
	return undef unless defined $_[0];

	# Parse the file
	my $ns      = '_';
	my $counter = 0;
	foreach ( split /(?:\015{1,2}\012|\015|\012)/, shift ) {
		$counter++;

		# Skip comments and empty lines
		next if /^\s*(?:\#|\;|$)/;

		# Handle section headers
		if ( /^\s*\[(.+?)\]\s*$/ ) {
			# Create the sub-hash if it doesn't exist.
			# Without this sections without keys will not
			# appear at all in the completed struct.
			$self->{$ns = $1} ||= {};
			next;
		}

		# Handle properties
		if ( /^\s*([^=]+?)\s*=\s*(.*?)\s*$/ ) {
			$self->{$ns}->{$1} = $2;
			next;
		}

		return $self->_error( "Syntax error at line $counter: '$_'" );
	}

	$self;
}

# Save an object to a file
sub write {
	my $self = shift;
	my $file = shift or return $self->_error( 'No file name provided' );

	# Write it to the file
	open( CFG, '>', $file ) 
		or return $self->_error( "Failed to open file '$file' for writing: $!" );
	print CFG $self->write_string;
	close CFG;
}

# Save an object to a string
sub write_string {
	my $self = shift;

	my $contents = '';
	foreach my $section ( sort { (($b eq '_') <=> ($a eq '_')) || ($a cmp $b) } keys %$self ) {
		my $block = $self->{$section};
		$contents .= "\n" if length $contents;
		$contents .= "[$section]\n" unless $section eq '_';
		foreach my $property ( sort keys %$block ) {
			$contents .= "$property=$block->{$property}\n";
		}
	}
	
	$contents;
}

# Error handling
sub errstr { $errstr }
sub _error { $errstr = $_[1]; undef }

1;

__END__

=pod

=head1 NAME

Config::Tiny - Read/Write .ini style files with as little code as possible

=head1 SYNOPSIS

    # In your configuration file
    rootproperty=blah

    [section]
    one=twp
    three= four
    Foo =Bar
    empty=

    # In your program
    use Config::Tiny;

    # Create a config
    my $Config = Config::Tiny->new();

    # Open the config
    $Config = Config::Tiny->read( 'file.conf' );

    # Reading properties
    my $rootproperty = $Config->{_}->{rootproperty};
    my $one = $Config->{section}->{one};
    my $Foo = $Config->{section}->{Foo};

    # Changing data
    $Config->{newsection} = { this => 'that' }; # Add a section
    $Config->{section}->{Foo} = 'Not Bar!';     # Change a value
    delete $Config->{_};                        # Delete a value or section

    # Save a config
    $Config->write( 'file.conf' );

=head1 DESCRIPTION

Config::Tiny is a perl class to read and write .ini style configuration files
with as little code as possible, reducing load time and memory overhead.
Memory usage is normally scoffed at in Perl, but in my opinion should be
at least kept in mind.

This module is primarily for reading human written files, and anything we
write shouldn't need to have documentation/comments. If you need something
with more power, move up to Config::Simple, Config::General or one of the
many other Config:: modules. To rephrase, Config::Tiny does not preserve
your comments, whitespace, or the order of your config file.

=head1 CONFIGURATION FILE SYNTAX

Files are the same as windows .ini files, for example.

	[section]
	var1=value1
	var2=value2

If a property is outside of a section, it will be assigned to the root
section, available at C<$Config-E<gt>{_}>.

Lines starting with '#' or ';' are comments, and blank lines are ignored.

When writing back to the config file, any comments are discarded.

=head1 METHODS

=head2 new

The constructor C<new> creates and returns an empty Config::Tiny object.

=head2 read $filename

The C<read> constructor reads a config file, and returns a new Config::Tiny
object containing the properties in the file. 

Returns the object on success, or C<undef> on error.

=head2 read_string $string;

The C<read_string> method takes as argument the contents of a config file as a string
and returns the Config::Tiny object for it.

=head2 write

The C<write $filename> generates the file for the properties, and writes it
to disk. 

Returns true on success or C<undef> on error.

=head2 write_string

Generates the file for the object and returns it as a string.

=head2 errstr

When an error occurs, you can retrieve the error message either from the
C<$Config::Tiny::errstr> variable, or using the C<errstr()> method.

=head1 SUPPORT

Bugs should be reported via the CPAN bug tracker at

  http://rt.cpan.org/NoAuth/ReportBug.html?Queue=Config%3A%3ATiny

For other issues, contact the author

=head1 TO DO

I'm debating adding a get and set method to get or set a section.key based
value...

Implementation is left as an exercise for the reader.

=head1 AUTHOR

        Adam Kennedy ( maintainer )
        cpan@ali.as
        http://ali.as/

Thanks to Sherzod Ruzmetov <sherzodr@cpan.org> for Config::Simple,
which inspired this module by being not quite "simple" enough for me :)

=head1 SEE ALSO

L<Config::Simple>, L<Config::General>

=head1 COPYRIGHT

Copyright 2002-2004 Adam Kennedy. All rights reserved.
This program is free software; you can redistribute
it and/or modify it under the same terms as Perl itself.

The full text of the license can be found in the
LICENSE file included with this module.

=cut
