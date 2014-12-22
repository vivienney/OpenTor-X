<?php

class Torrentmodel extends CI_Model
{

	function __constrcut()
	{
		parent::__construct();
	}

	function get($category = '', $limit = 12, $start=0, $search="", $limitcharacters=TRUE)
	{

		$this->load->library("Swiss_cache");

		$cache_name = $category.'_'.$limit.'_'.$start.'_'.$search;

		$data = $this->swiss_cache->get($cache_name);
		
		if($data){
			return $data;
		}		

		$this->load->helper('text');

		//If we failed to get cached data lets get new results!

		$this->db->select('*');

		if(in_array(strtolower($category), $this->get_categories()) && !empty($category))
		{
			$this->db->where('category', $category);
		}

		$this->db->limit($limit, $start);

		if(!empty($search))
		{
			$this->db->like('name', $search);
		}

		$query = $this->db->get('torrents');
		$this->db->flush_cache();

		$data = array();

		if($query->num_rows() == 0)
		{
			return $data;
		}

		foreach($query->result_array() as $row)
		{
			
			$row['name'] = character_limiter($row['name'], 40);
			$data[] = $row;
		}

		$this->swiss_cache->set($cache_name, $data, 18000);

		return $data;
	}

	function search($search)
	{
		$parts = explode(":", $search);
		$category = '';
		$qsearch = '';		

		$isCat = FALSE;
		if(count($parts) > 0)
		{
			if(in_array(strtolower($parts[0]), $this->get_categories()))
			{
				$category = $parts[0];
				$isCat = TRUE;
			}
		}

		if(count($parts) == 1 && !$isCat)
		{
			$qsearch = $parts[0];
		}
		else if(count($parts) > 1)
		{
			$qsearch = $parts[1];
		}

		return $this->get($category, 25, 0, $qsearch);
	}

	function get_torrent($hash)
	{
		$this->load->library("Swiss_cache");
		$cache_name = 'torrent_'.$hash;
		$data = $this->swiss_cache->get($cache_name);

		if($data){
			return $data;
		}

	}

	function get_categories()
	{
		return array('anime', 'movies', 'music', 'other', 'applications', 'games', 'tv', 'xxx', 'books');
	}

}
?>