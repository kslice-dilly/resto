#!/usr/bin/python
import mysql.connector
import json
import urllib
import uuid
import binascii
from db_cred import *

try:
	# Get API key from DB
	# @todo implement try/catch for api_connection
	api_conn = mysql.connector.connect(host='localhost', user=ro_user, password=ro_pw, database='google_api')
	sqlGet_key = ("SELECT `key` FROM api_keys WHERE name='geocode'")
	cur = api_conn.cursor()
	cur.execute(sqlGet_key)
	api_key = cur.fetchone()[0]
	# @todo implement round-robin selection of API key if multiple entries
	api_conn.close()

	api_url_prefix = ("https://maps.googleapis.com/maps/api/geocode/json?region=ca&address=")
	api_url_suffix = ("&key=" + api_key)
	sqlGet_records = ("SELECT bus_guid, addr FROM geocode WHERE hit=0")
	sqlUpdate_coords = ("UPDATE geocode SET `lat` = %3.7f, `long` = %3.7f, hit=1 "
			    "WHERE bus_guid = UNHEX('%s')")
	lat = 0
	lng = 0
	i = 0

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

	for row in cur_get.fetchall():	
		guid = uuid.UUID(binascii.b2a_hex(row[0]))

		# Fix URL so it has no spaces, special chars
		print("Fetching lat,long for address " + row[1])
		url = urllib.quote(api_url_prefix + row[1] + api_url_suffix, safe="%/:=&?~#+!$,;'@()*[]")
		i = i + 1
		j = json.load(urllib.urlopen(url, row[1]))
		if j['status'] == 'UNKNOWN_ERROR':
			# indicates that the request could not be processed due to a server error
			# Try again?
			#j = json.load(urllib.urlopen(url, row[1]))
			lat = 0
			lng = 0
		# @refactor to switch?
		if j['status'] == 'OVER_QUERY_LIMIT':
			# indicates that the quota has been reached for the current google geocode API key
			print('Over query limit for ' + api_key[0])
			exit(124)
		elif j['status'] == 'INVALID_REQUEST':
			# generally indicates that the query (address, componentes, latlng) is missing
			print('Invalid geocode request (url: ' + url)
			exit(125)
		elif j['status'] == 'ZERO_RESULTS':
			# indicates that the geocode was successful but returned no results.
			# -- this may occur if the geocoder was passed a non-existent address
			print('Zero result!')
			lat = 0
			lng = 0
		elif j['status'] == 'REQUEST_DENIED':
			# indicates that the request was denied; likely a bogus API key
			print('Geocode request denied.')
			exit(126)
		elif j['status'] == 'OK':
			# indicates that no errors occured; the address was successfully parsed and at least
			# one geocode was returned
			lat = j['results'][0]['geometry']['location']['lat']
			lng = j['results'][0]['geometry']['location']['lng']
			print('Found!: lat=%3.7f,lng=%3.7f' % (lat, lng))
		else:
			print('Unknown response: ' + j['status'])

		# Execute update on row
		print((sqlUpdate_coords % (lat,lng,guid.hex)))
		conn_put.cursor().execute(sqlUpdate_coords % (lat, lng, guid.hex))
		conn_put.commit()
		if i > 2000:
			print('User quota reached (2000)')
			exit(0)

except mysql.connector.Error as err:
	print(err)
else:
  conn.close()

exit(0)
