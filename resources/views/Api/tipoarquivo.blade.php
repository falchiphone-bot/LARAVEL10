
if($mime_type == 'image/jpeg' || $mime_type == 'image/png' || $mime_type == 'image/jpg')
{
   $tipoarquivo = 'image';
}
elseif($mime_type == 'video/mp4' || $mime_type == 'video/3gpp' || $mime_type == 'video/quicktime')
{
    $tipoarquivo = 'video';

}
elseif($mime_type == 'application/pdf'
        || $mime_type == 'application/msword'
         || $mime_type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
{
    $tipoarquivo = 'document';

    


}
else
{
    $tipoarquivo = 'text';
}
