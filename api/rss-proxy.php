<?php
header('Content-Type: application/json');

$url = isset($_GET['url']) ? $_GET['url'] : '';
$maxItems = isset($_GET['max']) ? (int)$_GET['max'] : 5;

if (empty($url)) {
    echo json_encode(['error' => 'URL de feed no proporcionada']);
    exit;
}

try {
    $content = @file_get_contents($url);
    
    if ($content === false) {
        echo json_encode(['error' => 'No se pudo cargar el feed RSS']);
        exit;
    }
    
    $xml = @simplexml_load_string($content);
    
    if ($xml === false) {
        echo json_encode(['error' => 'No se pudo analizar el feed RSS']);
        exit;
    }
    
    $result = [];
    
    // Procesar feed RSS estándar
    if (isset($xml->channel)) {
        $result['title'] = (string)$xml->channel->title;
        $result['description'] = (string)$xml->channel->description;
        $result['link'] = (string)$xml->channel->link;
        
        $result['items'] = [];
        $count = 0;
        
        foreach ($xml->channel->item as $item) {
            if ($count >= $maxItems) break;
            
            $result['items'][] = [
                'title' => (string)$item->title,
                'link' => (string)$item->link,
                'description' => (string)$item->description,
                'pubDate' => (string)$item->pubDate,
            ];
            
            $count++;
        }
    } 
    // Procesar feed Atom
    elseif (isset($xml->entry)) {
        $result['title'] = (string)$xml->title;
        $result['description'] = (string)$xml->subtitle;
        
        // Manejar el link en formato Atom
        $linkHref = '';
        foreach ($xml->link as $link) {
            if ((string)$link['rel'] === 'alternate' || (string)$link['rel'] === '' || (string)$link['rel'] === 'self') {
                $linkHref = (string)$link['href'];
                break;
            }
        }
        $result['link'] = $linkHref;
        
        $result['items'] = [];
        $count = 0;
        
        foreach ($xml->entry as $entry) {
            if ($count >= $maxItems) break;
            
            // Obtener el link del entry
            $entryLinkHref = '';
            foreach ($entry->link as $link) {
                if ((string)$link['rel'] === 'alternate' || (string)$link['rel'] === '') {
                    $entryLinkHref = (string)$link['href'];
                    break;
                }
            }
            
            $result['items'][] = [
                'title' => (string)$entry->title,
                'link' => $entryLinkHref,
                'description' => isset($entry->content) ? (string)$entry->content : (string)$entry->summary,
                'pubDate' => isset($entry->published) ? (string)$entry->published : (string)$entry->updated,
            ];
            
            $count++;
        }
    } else {
        echo json_encode(['error' => 'Formato de feed RSS no reconocido']);
        exit;
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
exit;
