
=head1 NAME

XML::RAX - Record-oriented API for XML

=head1 SYNOPSIS

use XML::RAX;
my $R = new XML::RAX();

# open from XML data
$R->open( '<Table><Record><ID>1</ID><Phone>555-5555</Phone></Record></Table>' );
$R->setRecord('Record');

# open XML from file
$R->openfile( 'test.xml' );
$R->setRecord('Record');

# iterate through recordset
my $rec = $R->readRecord();
while ( $rec )
	{
	print "Phone = ".$rec->getField('Phone')."\n";
	$rec = $R->readRecord();
	}

=head1 DESCRIPTION

This interface allows you to access an XML document as you 
would a database recordset. In instances where the XML document 
fits a record/field type format, using the RAX interface
will usually be simpler than using DOM or SAX to access the data.

XML::RAX requires XML::Parser.

See Sean McGrath's article on RAX for an good overview of RAX:
http://www.xml.com/pub/2000/04/26/rax/index.html

=head1 AUTHOR

Robert Hanson

=head1 CREDITS

The RAX API was created by Sean McGrath and first introduced in
his article on XML.com.

=head1 COPYRIGHT

Copyright (c) 2000 Robert Hanson. All rights reserved. This program 
is free software; you can redistribute it and/or modify it under 
the same terms as Perl itself. 

=cut

package XML::RAX;

use XML::Parser;
use FileHandle;
use strict;
use vars qw($VERSION);

$VERSION = "0.01";

sub new
	{
	my $class = shift;
	my $xml = shift;

	my $self = {
		'parse_done'    => 0,      # true when XML page is fully parsed
		'parse_started' => 0,      # true after parse is started
		'rax_opened'    => 0,      # true when data passed through open method
		'record_delim'  => '',     # record tag identifier
		'rec_fields'    => [],     # temp storage of a records fields 
		'records'       => [],     # queue of records
		'parser'        => undef,  # XML::Parser object
		'expatnb'       => undef,  # Expat::NB object
		'field_lvl'     => 0,      # numeric level where fields reside (rec_lvl + 1)
		'rec_lvl'       => 0,      # numeric level where records reside
		'in_rec'        => 0,      # true when parse is inside record_delim tag
		'in_field'      => 0,      # true when parse is inside field_lvl
		'field_data'    => '',     # text data within current field
		'tag_stack'     => [],     # stack of element tags
		'xml'           => '',     # xml text for parsing
		'xml_file'      => undef,  # filehandle to xml doc
		};
	bless $self, $class;
	
	return $self;
	}


sub debug
	{
	# comment out the next line to show debugging info
	return 1;

	# show parse info
	my $self = shift;
	my $source = shift;

	print "SOURCE: $source\n";
	foreach my $prop ( sort (keys(%{$self})) )
		{
		next if ( $prop =~ /^(?:xml|records|parser|expat|tag_stack)/ );
		print "$prop = $self->{$prop}\n";
		}
	print "Records: ".scalar(@{$self->{records}})."\n";
	print "====================================\n";
	return 1;
	}
	

sub open
	{
	my $self = shift;

	return 0 if ( $self->{rax_opened} );

	$self->{xml} = shift;
	$self->{rax_opened} = 1;
	return 1;
	}


sub openfile
	{
	my $self = shift;

	return 0 if ( $self->{rax_opened} );
	
	my $filename = shift;
	my $fh = FileHandle->new( $filename, 'r' );

	if ( defined($fh) )
		{
		$self->{xml_file} = $fh;
		$self->{rax_opened} = 1;
		return 1;
		}

	return 0;
	}


sub startparse
	{
	my $self = shift;

	$self->{parser} = new XML::Parser( 
	Handlers => {
		Start => sub { $self->handle_start(@_); },
		End   => sub { $self->handle_end(@_); },
		Char  => sub { $self->handle_char(@_); },
		Final => sub { $self->handle_final(@_); } } );

	$self->{expatnb} = $self->{parser}->parse_start();
	
	if ( $self->{expatnb} )
		{
		$self->{parse_started} = 1;
		return 1;
		}
	
	return 0;
	}

sub parse
	{
	my $self = shift;

	return 0 unless ( $self->{rax_opened} );
	return 0 if ( $self->{parse_done} );

	unless ( $self->{parse_started} )
		{
		$self->startparse() || return 0;
		}

	
	if ( defined $self->{xml_file} )
		{
		my $buffer;
		read( $self->{xml_file}, $buffer, 4096 );

		if ( length( $buffer ) )
			{
			$self->{expatnb}->parse_more( $buffer );
			}
		else
			{
			$self->{expatnb}->parse_done;
			$self->{parse_done} = 1;
			}
		}
	else
		{
		$self->{expatnb}->parse_more( $self->{xml} );
		$self->{expatnb}->parse_done;
		$self->{parse_done} = 1;
		}
	
	return 1;
	}

sub handle_start
	{
	my $self = shift;
	my ( $expat, $element, %attr ) = @_;
	
	push @{$self->{tag_stack}}, $element;

	if ( ( ! $self->{in_rec} ) && ( $element eq $self->{record_delim} ) )
		{
		$self->{in_rec} = 1;
		$self->{rec_lvl} = scalar(@{$self->{tag_stack}});
		$self->{field_lvl} = $self->{rec_lvl} + 1;
		}
	elsif ( ( $self->{in_rec} ) && ( scalar(@{$self->{tag_stack}}) == $self->{field_lvl} ) )
		{
		$self->{in_field} = 1;
		}

	$self->debug("->-> $element");
	}

sub handle_end
	{
	my $self = shift;
	my ( $expat, $element ) = @_;
	
	pop @{$self->{tag_stack}};
	
	if ( $self->{in_rec} )
		{
		if ( scalar(@{$self->{tag_stack}}) < $self->{rec_lvl} )
			{
			$self->{in_rec} = 0;
			push @{$self->{records}}, XML::RAX::Record->new( @{$self->{rec_fields}} );
			$self->{rec_fields} = [];
			}
		elsif ( scalar(@{$self->{tag_stack}}) < $self->{field_lvl} )
			{
			$self->{in_field} = 0;
			push @{$self->{rec_fields}}, { name => $element, value => $self->{field_data} };
			$self->{field_data} = '';
			}
		}
		
	$self->debug("<-<- $element");
	}

sub handle_char
	{
	my $self = shift;
	my ( $expat, $char ) = @_;

	if ( $self->{in_field} )
		{
		$self->{field_data} .= $char;
		}
	$self->debug("handle char");
	}

sub handle_final
	{
	my $self = shift;
	my $expat = shift;
	$self->debug("handle final");
	}

sub setRecord
	{
	my $self = shift;
	
	return 0 if ( $self->{parse_started} );

	$self->{record_delim} = shift;
	$self->debug("set record");
	return 1;
	}

sub readRecord
	{
	my $self = shift;

	$self->parse() until ( ( scalar @{$self->{records}} ) || ( $self->{parse_done} ) );
	
	$self->debug("read record");
	return shift( @{$self->{records}} );
	}


package XML::RAX::Record;

sub new
	{
	my $class = shift;
	my @fields = @_;

	my $self = { fields => {} };
	bless $self, $class;

	foreach my $field ( @fields )
		{
		$self->{fields}->{$field->{name}} = $field->{value};
		}

	return $self;
	}


sub getField
	{
	my $self = shift;
	my $field = shift;

	my $retval = $self->{fields}->{$field};
	$retval =~ s/^\s*(.*?)\s*$/$1/ if ( $retval );
	return $retval;
	}


1;
