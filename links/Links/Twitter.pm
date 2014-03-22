#!/usr/bin/perl

package Links::Twitter; 


require Exporter; 
require Links;

@ISA = (Exporter, Links);


@EXPORT = qw( new pre_parse_twitter );
 
sub new {

    my $this = shift;
    my $class = ref($this) || $this;
    #my $self = {};
    my $self = {@_ };
    
    bless $self, $class;
    
    printf "Initialize Twitter Object\n"; 
    
    #pre_parse_twitter($self);
    
    #extract_url($self);
    
    return $self;

}


sub pre_parse_twitter {

    use HTTP::Date qw(:DEFAULT parse_date);

    my $self = shift;

    #Get the date/time Wed Mar 23 04:31:46 +0000 2011
    @date_time = split( / /, $self->{'created_at'} );

    my ($year, $month, $day, $hour, $min, $sec, $tz) = parse_date($date_time[2] . "-" . $date_time[1] . "-" . $date_time[5] . " " . $date_time[3] . " GMT");
    #NEED TO FIX THIS !!!!!!!!!!!!!!!!!!!!!!!!!!
    $hour -= 6;
    $self->{'date'} = $year . "/" . $month . "/" . $day . " " . $hour . ":" . $min . ":" . $sec;

    #Parse the Announcer
    my $unparsed_announcer = $self->{'announcer'};
       $self->{'announcer'} = parse_announcer($unparsed_announcer);

    #Remove Carriage Returns and newlines
    $self->{'body'} =~ s/(\r|\n)//g;
    
    #Strip anonym.to so we can get the title
    $self->{'body'} =~ s/http:\/\/anonym\.to\/\?//i;

    #Set the Type
    $self->{'type'} = 'twitter';


}





sub parse_announcer {
    my ($nick) = @_;

    #Remove @ and + from nick
    $nick =~ s/[\+@]//g;
    
    #Remove trailing underscores
    $nick =~ s/_*$//g;

    my $main_nick = $nick;

    #Escape the Pipe so the regex doesn't try to evaluate it.
    $nick =~ s/\|/\\\|/g;

    foreach (@main::nicks) {
	if (/$nick/i) {

	    print "Original Nick: $main_nick \n" if $main::debug;

	    @alternate_nicks = split(/:/);
	    $main_nick         = $alternate_nicks[0];

	    print "New Nick: $main_nick \n" if $main::debug;

	    return $main_nick;
	}
    }

    return $main_nick;

}




    
1;
