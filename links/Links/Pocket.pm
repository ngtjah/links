#!/usr/bin/perl

package Links::Pocket; 


require Exporter; 
require Links;

@ISA = (Exporter, Links);


@EXPORT = qw( new pre_parse_pocket );
 
sub new {

    my $this = shift;
    my $class = ref($this) || $this;
    #my $self = {};
    my $self = {@_ };
    
    bless $self, $class;
    
    printf "Initialize Pocket Object\n"; 
    
    return $self;

}


sub pre_parse_pocket {

    use HTTP::Date qw(:DEFAULT parse_date);


    my $self = shift;

    my $localtime = scalar(localtime($self->{'time_added'}));
       $localtime =~ s/  / /g;

    #Get the date/time Wed Mar 23 04:31:46 +0000 2011
    #Mon Mar 18 13:57:48 2013
    @date_time = split( / /, $localtime );

    my ($year, $month, $day, $hour, $min, $sec, $tz) = parse_date($date_time[2] . "-" . $date_time[1] . "-" 
								  . $date_time[4] . " " . $date_time[3] . " GMT");

    $self->{'date'} = $year . "/" . $month . "/" . $day . " " . $hour . ":" . $min . ":" . $sec;
    

    #Parse the Announcer
    my $unparsed_announcer = $self->{'announcer'};
       $self->{'announcer'} = parse_announcer($unparsed_announcer);

    #Remove Carriage Returns and newlines
    $self->{'body'} =~ s/(\r|\n)//g;
    
    #Strip anonym.to so we can get the title
    $self->{'body'} =~ s/http:\/\/anonym\.to\/\?//i;

    #Set the Type
    $self->{'type'} = 'pocket';


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
