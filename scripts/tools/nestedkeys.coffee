###
# Get the key parts of a nested property
###
export keyparts = (key) ->
    key.split(new RegExp("\\[|\\]\\.?")).filter((v) -> v)

###
# Set a nested property on an object
###
export setkey = (obj, key, value) ->

    parts = keyparts(key)

    # Direct reference
    if parts.length == 1
        obj[key] = value
        return obj

    # Nested reference
    step = obj
    parts = keyparts(key)
    last = parts.pop()

    for part in parts
        step[part] = {} unless step[part]
        step = step[part]
    step[last] = value

    return obj

###
# Get a nested property on an object
###
export getkey = (obj, key, value) ->
    step = obj
    parts = keyparts(key)

    for part in parts
        return undefined unless step[part]
        step = step[part]

    return step