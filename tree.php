<?php

$categories = [
	0 => ['id' => 1, 'name' => 'Test category', 'parent_id' => 	null],
	1 => ['id' => 2, 'name' => 'Test category 2', 'parent_id' => null],
	2 => ['id' => 3, 'name' => 'Test child category', 'parent_id' => 1],
	3 => ['id' => 4, 'name' => 'Test child category2', 'parent_id' => 2],
	4 => ['id' => 5, 'name' => 'Test child category3', 'parent_id' => 4],
	5 => ['id' => 6, 'name' => 'Test child category4', 'parent_id' => 2],
];

foreach ($categories as $key => $value) {
	echo "<br>".$value['id']." --- ".$value['name']." --- ".$value['parent_id'];
}

$treeArr = [];
$treeArr = buildTree($categories);
echo "<br><br><pre>";
var_dump($treeArr);

function buildTree($categories, $lastCategory = null)
{
	foreach ($categories as $key => $value) {
		if ($value['parent_id'] == null && $lastCategory == null) {
			unset($categories[$key]);
			$newTreeArr = buildTree($categories, $value);
			$value['children'] = $newTreeArr;
			$treeArr[] = $value;
		} elseif ($value['parent_id'] == $lastCategory['id']) {
			unset($categories[$key]);
			$newTreeArr = buildTree($categories, $value);
			$value['children'] = $newTreeArr;
			$treeArr[] = $value;
		}
	}

	return $treeArr;
}