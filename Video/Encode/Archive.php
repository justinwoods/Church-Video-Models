<?php

class JW_Video_Encode_Archive extends JW_Video_Encode_Abstract
{

    protected $_encoder_settings = 
      '-r 29.97 -vcodec libx264 -s 480x272 -flags +loop -cmp +chroma -deblockalpha 0 -deblockbeta 0 -crf 24 -bt 256k -refs 1 -coder 0 -subq 5 -partitions +parti4x4+parti8x8+partp8x8 -g 250 -keyint_min 25 -level 30 -qmin 10 -qmax 51 -trellis 2 -sc_threshold 40 -i_qfactor 0.71 -acodec libfaac -ab 128k -ar 48000 -ac 2';

    public function getCommand()
    {
        return $this->_getCommand();
    }

}