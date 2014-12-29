########################################################
#        Eggdrop Logger 2.0 - www.mircstats.com        #
#        Logging in mIRC 6.17 format. 27/04/2006       #
#                                                      #
#   Make sure you have created the dir 'logger(dir)'   #
#                                                      #
#         Created by Lanze <simon@freeworld.dk>        #
#         Improved by zowtar <zowtar@gmail.com>        #
#                                                      #
########################################################


### Configuration


#;;; Where the logs will be saved.
set logger(dir) "mirclogs/"

#;;; Strip codes?
#;;; 0 = save codes\colors
#;;; 1 = no save codes\colors
set logger(strip) "1"

#;;; Save by Day, Week, Month or Disable?
set logger(time) "Month"



# # # # # # # # # # # # # # # #   DO NOT CHANGE ANYTHING BELOW HERE   # # # # # # # # # # # # # # # #



### Events
bind join - "#* *!*@*" logger:join
bind part - "#* *!*@*" logger:part
bind sign - "#* *!*@*" logger:quit
bind pubm - "#* *" logger:text
bind nick - "#* *" logger:nick
bind kick - "#* *" logger:kick
bind mode - "#* *" logger:mode
bind topc - "#* *" logger:topic
bind raw - "333" logger:topic-author
bind ctcp - "ACTION" logger:action

## Ex: [11:47AM:2000-07-06] <kapheine> go to http://kapheine.hypa.net

### Primary Commands
proc logger:join {nick uhost handle chan} {
  global logger botnick
  if {$nick == $botnick} {
    set log "[open "$logger(dir)$chan.log" a]"
    puts $log "\r"
    puts $log "Session Start: [strftime "%a %b %d %T %Y"]\r"
    puts $log "Session Ident: $chan\r"
    puts $log "\[[strftime "%H:%M:%Y-%m-%d"]\] * Now talking in $chan\r"
    close $log
  } else {
    logger:save $chan "* $nick ($uhost) has joined $chan"
  }
}

proc logger:part {nick uhost handle chan msg} {
  if {$msg == ""} {
    logger:save $chan "* $nick ($uhost) has left $chan"
  } else {
    logger:save $chan "* $nick ($uhost) has left $chan ($msg)"
  }
}

proc logger:quit {nick uhost handle chan reason} {
  logger:save $chan "* $nick ($uhost) Quit ($reason)"
}

proc logger:text {nick uhost handle chan text} {
  if {[isop $nick $chan] == "1"} {
    set nick "@$nick"
  } elseif {[ishalfop $nick $chan] == "1"} {
    set nick "%$nick"
  } elseif {[isvoice $nick $chan] == "1"} {
    set nick "+$nick"
  }
  logger:save $chan "<$nick> $text"
}

proc logger:nick {nick uhost handle chan newnick} {
  logger:save $chan "* $nick is now known as $newnick"
}

proc logger:kick {nick uhost handle chan target reason} {
  logger:save $chan "* $target was kicked by $nick ($reason)"
}

proc logger:mode {nick uhost handle chan mode victim} {
  logger:save $chan "* $nick sets mode: $mode $victim"
}

proc logger:topic {nick uhost handle chan topic} {
  if {$nick == "*"} {
    logger:save $chan "* Topic is '$topic'"
  } else {
    logger:save $chan "* $nick changes topic to '$topic'"
  }
}

proc logger:topic-author {from keyword text} {
  logger:save [lindex $text 1] "* Set by [lindex $text 2] on [strftime "%a %b %d %T" [lindex $text 3]]"
}

proc logger:action {nick uhost handle dest keyword text} {
  if {[validchan $dest] == "1"} {
    if {[isop $nick $dest] == "1"} {
      set nick "@$nick"
    } elseif {[ishalfop $nick $dest] == "1"} {
      set nick "%$nick"
    } elseif {[isvoice $nick $dest] == "1"} {
      set nick "+$nick"
    }
    logger:save $dest "* $nick $text"
  }
}


### Secondary Commands
proc logger:save {chan text} {
  global logger
  set log "[open "$logger(dir)$chan.log" a]"
  puts $log "\[[strftime "%H:%M:%S:%Y-%m-%d"]\] [logger:strip $text]\r"
  close $log
}


### Tertiary Commands
proc logger:strip {text} {
  global logger numversion
  if {$logger(strip) == "1"} {
    if {$numversion >= "1061700"} {
      set text "[stripcodes bcruag $text]"
    } else {
      regsub -all -- {\002,\003([0-9][0-9]?(,[0-9][0-9]?)?)?,\017,\026,\037} $text "" text
    }
  }
  return $text
}


### Time
proc logger:time {} {
  global logger
  foreach bind [binds time] {
    if {[string match "time * logger:time-save" $bind] == "1"} {
      unbind time - "[lindex $bind 2]" logger:time-save
    }
  }
  switch [string toupper $logger(time)] {
    DAY {
	bind time - "00 00 [[clock seconds] [expr [unixtime] + 86400]] * *" logger:time-save
    }
    WEEK {
	bind time - "00 00 [[clock seconds] [expr [unixtime] + ((7 - [strftime "%w"]) * 86400)]] * *" logger:time-save
    }
    MONTH {
      bind time - "00 00 01 [strftime "%m" [expr [unixtime] + ((32 - [clock seconds]) * 86400)]] *" logger:time-save
    }
  }
}

proc logger:time-save {minute hour day month year} {
  global logger
  foreach channel [channels] {
    if {[file exists "$logger(dir)$channel.log"] == "1"} {
      file rename -force "$logger(dir)$channel.log" "$logger(dir)${channel}_\[[strftime "%Y.%m.%d"]\].log"
    }
    set log "[open "$logger(dir)$channel.log" w]"
    puts $log "\r"
    puts $log "Session Start: [strftime "%a %b %d %T %Y"]\r"
    puts $log "Session Ident: $channel\r"
    puts $log "\[[strftime "%H:%M:%S:%Y-%m-%d"]\] * Now talking in $channel\r"
    close $log
    putquick "TOPIC $channel"
  }
  logger:time
}


logger:time


putlog "TCL Logger.tcl Loaded!"
