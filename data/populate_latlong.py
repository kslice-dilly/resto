#!/usr/bin/python
import mysql.connector
import json
import urllib
import uuid
import binascii
import time
from db_cred import *

def jsonRequest(url, data):
	return json.load(urllib.urlopen(url, data))

def reverseLookup(lat, lng):
	url_prefix = ("https://maps.googleapis.com/maps/api/geocode/json?latlng=")
	url_suffix = ("&key=" + api_key)
	j = jsonRequest(urllib.quote(api_url_prefix + lat + ',' + lng + api_url_suffix, safe="%/:=&?~#+!$,;'@()*[]"))
	if checkResult(j):
		print()

def checkResult(j):
	# @refactor to switch?
	if j['status'] == 'OVER_QUERY_LIMIT':
		# indicates that the quota has been reached for the current google geocode API key
		print('Over query limit (' + (used+i) + ') for key ' + api_key)
		exit(124)
	elif j['status'] == 'INVALID_REQUEST':
		# generally indicates that the query (address, componentes, latlng) is missing
		print('Invalid geocode request (url: ' + url)
		exit(125)
	elif j['status'] == 'ZERO_RESULTS':
		# indicates that the geocode was successful but returned no results.
		# -- this may occur if the geocoder was passed a non-existent address
		print('Zero result!')
	elif j['status'] == 'REQUEST_DENIED':
		# indicates that the request was denied; likely a bogus API key
		print('Geocode request denied.')
		exit(126)
	elif j['status'] == 'OK':
		# indicates that no errors occured; the address was successfully parsed and at least
		# one geocode was returned
		return True
	else:
		print('Unknown response: ' + j['status'])
	return False

try:
	# Get API key from DB
	conn_api = mysql.connector.connect(host='localhost', user=rw_user, password=rw_pw, database='google_api')
	sqlApi = ("SELECT `key` FROM api_keys WHERE name='geocode'")
	curApi = conn_api.cursor()
	curApi.execute(sqlApi)
	api_key = curApi.fetchone()[0]
	# @todo implement round-robin selection of API key if multiple entries
	sqlApi = ("SELECT quota,quota_counters.count FROM quotas JOIN (api_keys, quota_counters) "
		  "ON (quotas.api_id = api_keys.id AND quotas.id = quota_counters.quota_id) "
		  "WHERE api_keys.name='geocode'")
	curApi.execute(sqlApi)
	# @todo implement multiple quotas (daily, per minute)
	quota = curApi.fetchone() # unpack this
	used = quota[1]
	quota = quota[0]
	print('Today\'s quota: %i/%i (%3.1f %%).' % (used,quota,used/quota))
	if used >= quota:
		print('Quota has been reached.')
		exit(0)

	api_url_prefix = ("https://maps.googleapis.com/maps/api/geocode/json?region=ca&address=")
	api_url_suffix = ("&key=" + api_key)
	sqlGet_records = ("SELECT bus_guid, addr, lat, `long` FROM geocode WHERE hit=0")
	sqlUpdate_coords = ("UPDATE geocode SET `lat` = %3.7f, `long` = %3.7f, hit=1 "
			    "WHERE bus_guid = UNHEX('%s')")
	lat = 0
	lng = 0
	i = 0
	now = time.strftime("%Y%m%d")

	conn_put = mysql.connector.connect(host='localhost', 
					user=rw_user, 
					password=rw_pw, 
					database='resto')
	conn_get = mysql.connector.connect(host='localhost', 
					user=ro_user, 
					password=ro_pw, 
					database='resto')
	cur_get = conn_get.cursor(buffered=True)
	cur_get.execute(sqlGet_records)
	cur_put = conn_put.cursor()
	sqlApi = ("UPDATE quota_counters SET `count` = CASE "
		  "WHEN DATEDIFF(NOW(), `startdate`) >= 1 THEN 0 "
			"ELSE count END,"
		  "`startdate` = NOW() "
		  "WHERE quota_id = ( "
			"SELECT quotas.id FROM quotas JOIN (api_keys) ON (quotas.api_id = api_keys.id) "
			"WHERE api_keys.name = 'geocode')")
	curApi.execute(sqlApi)
	conn_api.commit()

	for row in cur_get.fetchall():	
		if (used + i) >= quota:
			print('User quota reached! (' + quota + ')')
			exit(0)

		guid = uuid.UUID(binascii.b2a_hex(row[0]))
		# Fix URL so it has no spaces, special chars
		print('Fetching lat,long for address ' + row[1])
		url = urllib.quote(api_url_prefix + row[1] + api_url_suffix, safe="%/:=&?~#+!$,;'@()*[]")
		i = i + 1
		errCount = 0
		j = jsonRequest(url, row[1])
		sqlApi = ("UPDATE quota_counters SET `count` = `count` + 1 WHERE quota_id")
		curApi.execute(sqlApi)
		conn_api.commit()

		while (j['status'] == 'UNKNOWN_ERROR') & (errCount < 3):
			# indicates that the request could not be processed due to a server error
			errCount = errCount + 1
			j = jsonRequest(url, row[1])
			curApi.execute(sqlApi) # increase counter again
			conn_api.commit()
		if checkResult(j):
			lat = j['results'][0]['geometry']['location']['lat']
			lng = j['results'][0]['geometry']['location']['lng']
			print('Found!: lat=%3.7f,lng=%3.7f' % (lat, lng))
		# Execute update on row
		conn_put.cursor().execute(sqlUpdate_coords % (lat, lng, guid.hex))
		conn_put.commit()

except mysql.connector.Error as err:
	print(err)
else:
	conn.close()
	api_conn.close()

exit(0)


